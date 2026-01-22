<?php

namespace plugin\control\utils\yidevs\trait;

use plugin\control\utils\yidevs\Client;

trait Video
{
    /**
     * 获取壹定开放平台大模型-视频-模型列表
     * @param int $channels_uid 分站使用$request->uid，客户端使用$request->channels_uid
     * @param array $data
     * @return array
     */
    public static function VideoModels(int $channels_uid, array $data = [])
    {
        $Client = new Client();
        $Client->setChannelsUid($channels_uid);
        return $Client->get('app/yimind/api/Video/models', $data);
    }
    /**
     * 调用壹定开放平台大模型-视频-生成
     * @param int $channels_uid 分站使用$request->uid，客户端使用$request->channels_uid
     * @param array $data
     * @return array
     */
    public static function VideoTITV(int $channels_uid, array $data = [])
    {
        $Client = new Client();
        $Client->setChannelsUid($channels_uid);
        return $Client->post('app/yimind/api/Video/titv', $data);
    }
}
