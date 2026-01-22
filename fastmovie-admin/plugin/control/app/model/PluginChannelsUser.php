<?php

namespace plugin\control\app\model;

use app\expose\utils\Password;
use app\expose\utils\Rsa;
use app\expose\utils\Str;
use app\model\Basic;
use Exception;
use loong\oauth\facade\Auth;
use plugin\control\expose\helper\Uploads;
use plugin\user\utils\enum\UserPermission;

class PluginChannelsUser extends Basic
{
    public function getHeadimgAttr($value, $data)
    {
        $id = $data['channels_uid'] ?? 0;
        return Uploads::url($data['id'], $value);
    }
    public function setHeadimgAttr($value, $data)
    {
        $id = $data['channels_uid'] ?? 0;
        return Uploads::path($id, $value);
    }
    public function setPasswordAttr($value)
    {
        return $value ? Password::encrypt($value) : $value;
    }
    public static function options($where = [])
    {
        $models = self::where($where)->field('id,nickname,mobile')->select();
        $data = [];
        foreach ($models as $item) {
            $data[] = [
                'label' => $item->nickname,
                'value' => $item->id,
                'tips' => "UID：{$item->id}，M：{$item->mobile}"
            ];
        }
        return $data;
    }
    public static function genUser($id)
    {
        $id = (int)$id;
        return str_replace(['+', '/'], ['-', '_'], Rsa::encryptNumber($id));
    }
    public static function getUidByUser($user)
    {
        return Rsa::decryptNumber(str_replace(['-', '_'], ['+', '/'], $user));
    }
    public static function getTokenInfo($model, $twofa = false)
    {
        $request = request();
        /* 重组用户信息 */
        $User = new \stdClass;
        $User->user = self::genUser($model->id);
        $User->nickname = $model->nickname;
        $User->headimg = $model->headimg;
        $User->username = $model->username;
        $User->username_time = $model->username_time ? 30 - ceil((time() - strtotime($model->username_time)) / 86400) : 0;
        $User->mobile = Str::mask($model->mobile);
        $User->email = Str::mask($model->email);
        $User->password = $model->password ? 1 : 0;
        $User->create_time = $model->create_time;
        $User->last_login_time = $model->login_time;
        $User->last_login_ip = $model->login_ip;
        $User->twofa_state = $model->twofa_state;
        $PluginChannelsWechat = PluginChannelsWechat::where(['uid' => $model->id])->field('openid,unionid,mp_openid,nickname,headimg,subscribe')->find();
        if ($PluginChannelsWechat) {
            $User->wechat = $PluginChannelsWechat;
        }
        if ($model->channels_uid === null) {
            $User->is_system = 1;
            $User->permissions = null;
            $User->channels_uid = $model->id;
        } else {
            $role = PluginChannelsRole::where(['id' => $model->role_id, 'channels_uid' => $model->channels_uid])->find();
            if (!$role) {
                throw new Exception("无权限访问");
            }
            # 是否为主账号
            $User->is_system = 0;
            # 不为主账号则所拥有的权限列表
            $User->permissions = $role->rule;
        }
        $pluginConfig = glob(base_path("plugin/*/api/{$request->app}/PublicController.php"));
        foreach ($pluginConfig as $path) {
            $plugin_name = basename(dirname(dirname(dirname($path))));
            if ($plugin_name == 'user') {
                continue;
            }
            $class = 'plugin\\' . $plugin_name . "\\api\\{$request->app}\\PublicController";
            if (!class_exists($class)) {
                continue;
            }
            $plugin = new $class;
            if (method_exists($plugin, 'appendUserInfo')) {
                $plugin->appendUserInfo($User, $model);
            }
        }
        /* 生成token */
        $data = new \stdClass;
        $data->uid = $model->id;
        $data->username = $model->username;
        $data->mobile = $model->mobile;
        $data->email = $model->email;
        $data->channels_uid = $User->channels_uid;
        $data->is_system = $User->is_system;
        $data->permissions = $User->permissions;
        $data->twofa_state = $model->twofa_state;
        if ($twofa) {
            if ($twofa === true) {
                $data->twofa = [
                    'expire' => time() + (config('oauth.expire') / 2),
                    'time' => time(),
                ];
            } else {
                $data->twofa = $twofa;
            }
        }
        $User->token = Auth::setPrefix('CONTROL')->encrypt($data);
        if ($request->token) {
            Auth::setPrefix('CONTROL')->refresh($request->token, 60);
        }
        return $User;
    }
    public static function onAfterRead($model)
    {
        if (empty($model->nickname)) {
            if (!empty($model->mobile)) {
                $model->nickname = 'XH-' . substr($model->mobile, -4);
            } else {
                $model->nickname = '未命名的用户';
            }
        }
    }
    public static function onBeforeWrite($model)
    {
        if (empty($model->nickname)) {
            if (!empty($model->mobile)) {
                $model->nickname = 'XH-' . substr($model->mobile, -4);
            } else {
                $model->nickname = 'XH-' . Str::random();
            }
        } else {
            $model->nickname = iconv('UTF-8', 'UTF-8//IGNORE', $model->nickname);
        }
    }


    public function role(){
        return $this->hasOne(PluginChannelsRole::class, 'id', 'role_id');
    }
}
