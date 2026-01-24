<?php

namespace plugin\user\expose\helper;

use app\expose\enum\State;
use plugin\user\app\model\PluginUser;
use plugin\user\app\model\PluginUserInvitationCode;
use plugin\user\app\model\PluginUserWechat;
use support\Redis;

class WechatOfficialAccount
{
    public $FromUserName;
    public $ToUserName;
    public $MsgType;
    public $Event;
    public $EventKey;
    public $data;
    public function bind($params)
    {
        $PluginUserWechat = PluginUserWechat::where('openid', $this->FromUserName)->find();
        if ($PluginUserWechat) {
            if ($PluginUserWechat->uid && $PluginUserWechat->uid != $params['uid']) {
                Redis::set($this->EventKey . '.bind', json_encode([
                    'status' => 'fail',
                    'message' => '当前微信已绑定其他账号'
                ]), 'EX', 60);
                throw new \Exception('当前微信已绑定其他账号');
            }
        } else {
            $PluginUserWechat = new PluginUserWechat();
            $PluginUserWechat->openid = $this->FromUserName;
        }
        $PluginUserWechat->uid = $params['uid'];
        $PluginUserWechat->subscribe = State::YES['value'];
        $PluginUserWechat->channels_uid = $params['channels_uid'];
        $PluginUserWechat->save();
        Redis::set($this->EventKey . '.bind', json_encode([
            'status' => 'success',
            'uid' => $params['uid']
        ]), 'EX', 60);
        return '绑定成功';
    }
    public function login($params)
    {
        try {
            $PluginUserInvitationCode = null;
            if (!empty($params['code'])) {
                $PluginUserInvitationCode = PluginUserInvitationCode::where(['code' => $params['code']])->find();
                if (!$PluginUserInvitationCode) {
                    $PluginUserInvitationCode = null;
                }
                if ($PluginUserInvitationCode->status != 'unused') {
                    $PluginUserInvitationCode = null;
                }
                if ($PluginUserInvitationCode->state != State::YES['value']) {
                    $PluginUserInvitationCode = null;
                }
            }

            $PluginUserWechat = PluginUserWechat::where('openid', $this->FromUserName)->find();
            if ($PluginUserWechat) {
                if ($PluginUserWechat->uid) {
                    Redis::set($this->EventKey . '.login', json_encode([
                        'status' => 'success',
                        'uid' => $PluginUserWechat->uid,
                    ]), 'EX', 60);
                    return '登录成功';
                }
            } else {
                $PluginUserWechat = new PluginUserWechat();
                $PluginUserWechat->openid = $this->FromUserName;
                $PluginUserWechat->subscribe = State::YES['value'];
                $PluginUserWechat->channels_uid = $params['channels_uid'];
            }
            $data = [
                'channels_uid' => $params['channels_uid'],
                'username' => $this->FromUserName,
                'state' => State::YES['value'],
            ];

            if ($PluginUserInvitationCode != null) {
                $data['puid'] = $PluginUserInvitationCode->uid;
                $data['activation_time'] = date('Y-m-d H:i:s');
            }
            $UserModel = new PluginUser();
            $UserModel->save($data);

            if ($PluginUserInvitationCode != null) {
                $PluginUserInvitationCode->status = 'used';
                $PluginUserInvitationCode->use_uid = $UserModel->id;
                $PluginUserInvitationCode->use_time = date('Y-m-d H:i:s');
                $PluginUserInvitationCode->save();
            }
            $PluginUserWechat->uid = $UserModel->id;
            $PluginUserWechat->save();
            Redis::set($this->EventKey . '.login', json_encode([
                'status' => 'success',
                'id' => $PluginUserWechat->id,
                'uid' => $UserModel->id
            ]), 'EX', 60);
            return '登录成功';
        } catch (\Throwable $th) {
            Redis::set($this->EventKey . '.login', json_encode([
                'status' => 'fail',
                'message' => $th->getMessage()
            ]), 'EX', 60);
            return $th->getMessage();
        }
    }
}
