<?php

namespace plugin\control\utils\yidevs\trait;

use plugin\control\utils\yidevs\Client;

trait VideoAssistant
{
    /**
     * 获取壹定开放平台大模型-绘图助手列表
     * @param int $channels_uid 分站使用$request->uid，客户端使用$request->channels_uid
     * @param array $query
     * @return array
     */
    public static function VideoAssistantlist(int $channels_uid, array $query = [])
    {
        $Client = new Client();
        $Client->setChannelsUid($channels_uid);
        return $Client->get('app/yimind/api/VideoAssistant/list', $query);
    }
    /**
     * 调用壹定开放平台大模型-绘图助手
     * @param int $channels_uid 分站使用$request->uid，客户端使用$request->channels_uid
     * @param array $data
     * @return array
     */
    public static function VideoAssistantTITV(int $channels_uid, array $data = [])
    {
        $Client = new Client();
        $Client->setChannelsUid($channels_uid);
        return $Client->post('app/yimind/api/VideoAssistant/titv', $data);
    }
}
