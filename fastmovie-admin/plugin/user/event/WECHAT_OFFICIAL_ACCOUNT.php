<?php

namespace plugin\user\event;

use app\expose\enum\State;
use plugin\user\app\model\PluginUserWechat;

class WECHAT_OFFICIAL_ACCOUNT
{
    public function subscribe(mixed $data)
    {
        $UserWechat = PluginUserWechat::where('openid', $data['FromUserName'])->find();
        if ($UserWechat) {
            $UserWechat->subscribe = State::YES['value'];
            $UserWechat->save();
        } else {
            $UserWechat = new PluginUserWechat();
            $UserWechat->channels_uid = $data['channels_uid'];
            $UserWechat->openid = $data['FromUserName'];
            $UserWechat->subscribe = State::YES['value'];
            $UserWechat->save();
        }
    }
    public function unsubscribe(mixed $data)
    {
        $UserWechat = PluginUserWechat::where('openid', $data['FromUserName'])->find();
        if ($UserWechat) {
            $UserWechat->subscribe = State::NO['value'];
            $UserWechat->save();
        }
    }
}
