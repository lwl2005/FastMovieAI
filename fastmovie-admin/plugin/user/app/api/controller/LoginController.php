<?php

namespace plugin\user\app\api\controller;

use app\Basic;
use app\expose\enum\ResponseCode;
use app\expose\enum\State;
use app\expose\exception\Exception;
use app\expose\helper\Captcha;
use app\expose\helper\Config;
use plugin\control\expose\helper\Vcode;
use app\expose\utils\Password;
use plugin\control\expose\helper\Wechat;
use plugin\user\app\model\PluginUser;
use plugin\user\app\model\PluginUserInvitationCode;
use plugin\user\expose\helper\User;
use plugin\user\utils\enum\VcodeScene;
use support\Log;
use support\Redis;
use support\Request;
use think\facade\Db;

class LoginController extends Basic
{
    protected $notNeedLoginAll = true;
    public function login(Request $request)
    {
        $D = $request->post();
        $where = [];
        # 判断$D['username']是用户名，11位手机号，邮箱
        if (empty($D['username'])) {
            return $this->fail('请输入用户名|手机号|邮箱');
        }
        $username = $D['username'];
        if (preg_match('/^\d{11}$/', $username)) {
            // 11位手机号
            $where[] = ['mobile', '=', $username];
        } elseif (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            // 邮箱
            $where[] = ['email', '=', $username];
        } else {
            // 用户名
            $where[] = ['username', '=', $username];
        }
        $where[] = ['channels_uid', '=', $request->channels_uid];
        $user = PluginUser::where($where)->find();
        if (!$user) {
            return $this->fail('用户不存在');
        }
        // 验证登录密码
        if (!Password::verify((string) $D['password'], (string) $user->password)) {
            return $this->fail('密码错误');
        }
        if ($user->state == State::NO['value']) {
            return $this->fail('用户状态异常');
        }
        $captcha_state = Config::get('captcha', 'state');
        // 检验图像验证码
        if ($captcha_state) {
            if (empty($D['captcha'])) {
                return $this->fail('请输入验证码');
            }
            $token = $D['token'] ?? null;
            if (!Captcha::check($D['captcha'], $token)) {
                return $this->fail('验证码错误');
            }
        }

        // 更新登录信息
        $ip = $request->getRealIp();
        $user->login_ip = $ip;
        $user->login_time = date('Y-m-d H:i:s');
        if ($user->save() === false) {
            return $this->fail('登录失败');
        }
        $info = PluginUser::getTokenInfo($user);
        return $this->resData($info);
    }
    public function vcode(Request $request)
    {
        Db::startTrans();
        try {
            $username = $request->post('username');
            $vcode = $request->post('vcode');
            $token = $request->post('token');
            $code = $request->header('X-Icode') ?? null;
            Vcode::check($username, $vcode, VcodeScene::SIGNUP['value'], $token);
            $PluginUserInvitationCode = null;
            if ($code) {
                $PluginUserInvitationCode = PluginUserInvitationCode::where(['code' => $code])->find();
                if (!$PluginUserInvitationCode) {
                    $PluginUserInvitationCode = null;
                }
                if ($PluginUserInvitationCode->status != 'unused') {
                    $PluginUserInvitationCode = null;
                }
            }
            $user = PluginUser::where('mobile', $username)->find();
            if (!$user) {
                $data = [
                    'channels_uid' => $request->channels_uid,
                    'mobile' => $username,
                    'state' => State::YES['value'],
                ];
                if ($PluginUserInvitationCode != null) {
                    $data['puid'] = $PluginUserInvitationCode->uid;
                    $data['activation_time'] = date('Y-m-d H:i:s');
                }
                $user = new PluginUser();
                $user->save($data);
            }
            if ($user->state == State::NO['value']) {
                throw new Exception('用户状态异常');
            }
            // 更新登录信息
            $ip = $request->getRealIp();
            $user->login_ip = $ip;
            $user->login_time = date('Y-m-d H:i:s');
            if ($PluginUserInvitationCode != null) {
                $PluginUserInvitationCode->status = 'used';
                $PluginUserInvitationCode->use_uid = $user->id;
                $PluginUserInvitationCode->use_time = date('Y-m-d H:i:s');
                $PluginUserInvitationCode->save();
            }
            if ($user->save() === false) {
                throw new Exception('登录失败');
            }
            $info = PluginUser::getTokenInfo($user);
            Db::commit();
            return $this->resData($info);
        } catch (\Throwable $th) {
            Db::rollback();
            return $this->exception($th);
        }
    }
    public function qrcode(Request $request)
    {
        return $this->wechat($request);
    }
    public function wechat(Request $request)
    {
        if ($request->isWehcatBrowser && $request->isMobile) {
        } else {
            if (!empty($request->uid)) {
                try {
                    $code = $request->header('X-Icode') ?? null;
                    $res = Wechat::createQrCode(Wechat::QR_STR_SCENE, 600, [
                        'plugin\user\expose\helper\WechatOfficialAccount',
                        'bind',
                        [
                            'channels_uid' => $request->channels_uid,
                            'code' => $code,
                            'uid' => $request->uid,
                        ]
                    ]);
                    return $this->resData(['qrcode' => $res['url'], 'id' => $res['id'], 'expire' => strtotime("+" . $res['expire_seconds'] . " seconds")]);
                } catch (Exception $e) {
                    p($e->getMessage());
                    Log::info($e->getMessage(), $e->getTrace());
                } catch (\Throwable $th) {
                    return $this->exception($th);
                }
            }
            try {
                $code = $request->header('X-Icode') ?? null;
                $res = Wechat::createQrCode(Wechat::QR_STR_SCENE, 600, [
                    'plugin\user\expose\helper\WechatOfficialAccount',
                    'login',
                    [
                        'channels_uid' => $request->channels_uid,
                        'code' => $code,
                    ]
                ]);
                return $this->resData(['qrcode' => $res['url'], 'id' => $res['id'], 'expire' => strtotime("+" . $res['expire_seconds'] . " seconds")]);
            } catch (Exception $e) {
                p($e->getMessage());
                Log::info($e->getMessage(), $e->getTrace());
            } catch (\Throwable $th) {
                return $this->exception($th);
            }
            try {
                $res = Wechat::createQrCode(Wechat::QR_SCENE, 600, [
                    'plugin\user\expose\helper\WechatOfficialAccount',
                    'scan',
                    [
                        'channels_uid' => $request->channels_uid,
                        'code' => $code,
                    ]
                ]);
                return $this->resData(['qrcode' => $res['url'], 'id' => $res['id'], 'expire' => strtotime("+" . $res['expire_seconds'] . " seconds")]);
            } catch (Exception $e) {
                Log::info($e->getMessage(), $e->getTrace());
            } catch (\Throwable $th) {
                return $this->exception($th);
            }
            /* $auth_url='';
            return $this->code(ResponseCode::OPEN_NEW_WINDOW, '请在微信中打开', [
                'url' => $auth_url
            ]); */
            return $this->fail('无可用的微信服务，请联系客服');
        }
    }
    public function checkQrcode(Request $request)
    {
        $id = $request->post('id');
        if (!$id) {
            return $this->fail('参数错误');
        }
        $Scene = Redis::get($id);
        if (!$Scene) {
            return $this->fail('二维码已过期');
        }
        $res = Redis::get($id . '.login');
        if ($res) {
            $res = json_decode($res, true);
            if ($res['status'] == 'success') {
                $user = PluginUser::where('id', $res['uid'])->find();
                if ($user) {
                    // 更新登录信息
                    $ip = $request->getRealIp();
                    $user->login_ip = $ip;
                    $user->login_time = date('Y-m-d H:i:s');
                    if ($user->save() === false) {
                        return $this->fail('登录失败');
                    }
                    $info = PluginUser::getTokenInfo($user);
                    return $this->resData($info);
                } else {
                    return $this->fail('登录失败');
                }
            } else {
                return $this->fail($res['message']);
            }
        }
        return $this->code(ResponseCode::WAIT, '等待微信扫码');
    }

    public function checkBind(Request $request)
    {
        $id = $request->post('id');
        if (!$id) {
            return $this->fail('参数错误');
        }
        $Scene = Redis::get($id);
        if (!$Scene) {
            return $this->fail('二维码已过期');
        }
        $res = Redis::get($id . '.bind');
        if ($res) {
            $res = json_decode($res, true);
            if ($res['status'] == 'success') {
                return $this->resData(['status' => 'success']);
            } else {
                return $this->fail($res['message']);
            }
        }
        return $this->code(ResponseCode::WAIT, '等待微信扫码');
    }
}
