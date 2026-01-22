<?php

namespace plugin\notification\app\api\controller;

use app\Basic;
use app\expose\enum\State;
use plugin\finance\app\model\PluginFinanceOrders;
use plugin\notification\utils\Api;
use plugin\shortplay\app\model\PluginShortplayDrama;
use plugin\user\app\model\PluginUser;
use plugin\notification\utils\enum\AuthType;
use support\Request;

class PushController extends Basic
{
    public function auth(Request $request)
    {

        $pusher = new Api(str_replace('0.0.0.0', '127.0.0.1', config('plugin.webman.push.app.api')), config('plugin.webman.push.app.app_key'), config('plugin.webman.push.app.app_secret'));
        $channel_name = $request->post('channel_name');
        $authType = AuthType::getValues();
        $match = preg_match('/^private-(' . implode('|', $authType) . ')-(.+)$/', $channel_name, $matches);
        if (!$match) {
            return $this->fail('Forbidden');
        }
        $header = $matches[1];
        $AuthTypeAction = AuthType::get($header);
        $hash = $matches[2];
        $has_authority = false;
        switch ($AuthTypeAction['action']) {
            case 'user':
                $uid = PluginUser::getUidByUser($hash);
                if ($uid != $request->uid) {
                    return $this->fail('Forbidden');
                }
                $PluginUser = PluginUser::where('id', $request->uid)->find();
                $has_authority = $PluginUser && $PluginUser->state == State::YES['value'];
                break;
            case 'orders':
                $PluginFinanceOrders = PluginFinanceOrders::where('trade_no', $hash)->find();
                if (!$PluginFinanceOrders) {
                    return $this->fail('Forbidden');
                }
                $has_authority = $PluginFinanceOrders->uid == $request->uid;
                break;
            case 'drama':
                $PluginShortplayDrama = PluginShortplayDrama::where(['id' => $hash, 'uid' => $request->uid])->find();
                if (!$PluginShortplayDrama) {
                    return $this->fail('Forbidden');
                }
                $has_authority = true;
                break;
        }
        if ($has_authority) {
            return $this->resData(json_decode($pusher->socketAuth($channel_name, $request->post('socket_id')), true));
        } else {
            return $this->fail('Forbidden');
        }
    }
}
