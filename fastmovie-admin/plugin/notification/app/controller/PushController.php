<?php

namespace plugin\notification\app\controller;

use app\Basic;
use plugin\finance\app\model\PluginFinanceOrders;
use plugin\notification\app\model\PluginNotificationOnline;
use plugin\notification\utils\enum\AuthType;
use plugin\shortplay\app\model\PluginShortplayDrama;
use plugin\user\app\model\PluginUser;
use support\Request;

class PushController extends Basic
{
    public function hook(Request $request)
    {
        // 没有x-pusher-signature头视为伪造请求
        if (!$webhook_signature = $request->header('x-pusher-signature')) {
            return response('401 Not authenticated', 401);
        }

        $body = $request->rawBody();

        // 计算签名，$app_secret 是双方使用的密钥，是保密的，外部无从得知
        $expected_signature = hash_hmac('sha256', $body, config('plugin.webman.push.app.app_secret'), false);

        // 安全校验，如果签名不一致可能是伪造的请求，返回401状态码
        if ($webhook_signature !== $expected_signature) {
            return response('401 Not authenticated', 401);
        }

        // 这里存储这上线 下线的channel数据
        $payload = json_decode($body, true);
        $channels_online = $channels_offline = [];

        foreach ($payload['events'] as $event) {
            if ($event['name'] === 'channel_added') {
                $channels_online[] = $event['channel'];
            } else if ($event['name'] === 'channel_removed') {
                $channels_offline[] = $event['channel'];
            }
        }

        // 业务根据需要处理上下线的channel，例如将在线状态写入数据库，通知其它channel等
        // 上线的所有channel
        // echo 'online channels: ' . implode(',', $channels_online) . "\n";
        foreach ($channels_online as $channel) {
            $PluginNotificationOnline = PluginNotificationOnline::where(['channel' => $channel])->find();
            if ($PluginNotificationOnline) {
                continue;
            }
            $authType = AuthType::getValues();
            $match = preg_match('/^private-(' . implode('|', $authType) . ')-(.+)$/', $channel, $matches);
            if (!$match) {
                continue;
            }
            $header = $matches[1];
            $hash = $matches[2];
            $PluginNotificationOnline = new PluginNotificationOnline();
            $PluginNotificationOnline->channel = $channel;
            $PluginNotificationOnline->event = $header;
            $PluginNotificationOnline->hash = $hash;

            $AuthTypeAction = AuthType::get($header);
            switch ($AuthTypeAction['action']) {
                case 'user':
                    $PluginNotificationOnline->uid = PluginUser::getUidByUser($hash);
                    $PluginUser = PluginUser::where('id', $PluginNotificationOnline->uid)->find();
                    $PluginNotificationOnline->channels_uid = $PluginUser->channels_uid;
                    break;
                case 'orders':
                    $PluginFinanceOrders = PluginFinanceOrders::where('trade_no', $hash)->find();
                    if (!$PluginFinanceOrders) {
                        break;
                    }
                    $PluginNotificationOnline->uid = $PluginFinanceOrders->uid;
                    $PluginNotificationOnline->channels_uid = $PluginFinanceOrders->channels_uid;
                    break;
                case 'drama':
                    $PluginShortplayDrama = PluginShortplayDrama::where(['id' => $hash])->find();
                    if (!$PluginShortplayDrama) {
                        break;
                    }
                    $PluginNotificationOnline->uid = $PluginShortplayDrama->uid;
                    $PluginNotificationOnline->channels_uid = $PluginShortplayDrama->channels_uid;
                    break;
            }
            $PluginNotificationOnline->save();
        }
        // 下线的所有channel
        // echo 'offline channels: ' . implode(',', $channels_offline) . "\n";
        foreach ($channels_offline as $channel) {
            $PluginNotificationOnline = PluginNotificationOnline::where(['channel' => $channel])->find();
            if (!$PluginNotificationOnline) {
                continue;
            }
            $PluginNotificationOnline->delete();
        }
        return 'OK';
    }
}
