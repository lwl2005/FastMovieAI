<?php

namespace plugin\article\process;

use app\expose\enum\Examine;
use app\expose\enum\State;
use plugin\article\app\model\PluginArticle;
use plugin\article\app\model\PluginArticleContent;
use plugin\notification\app\model\PluginNotificationMessage;
use support\Log;
use support\think\Db;
use Workerman\Crontab\Crontab;

class JoinPushMessage
{
    public function onWorkerStart()
    {
        new Crontab('*/5 * * * * *', function () {
            try {
                $where = [];
                $where[] = ['state', '=', State::YES['value']];
                $where[] = ['examine', '<', Examine::PASS['value']];
                $where[] = ['release_time', '<=', date('Y-m-d H:i:s')];
                $where[] = ['last_heartbeat', '<=', date('Y-m-d H:i:s')];
                $where[] = ['push_state', '=', State::YES['value']];
                $PluginArticle = PluginArticle::where($where)->find();
                if (!$PluginArticle) {
                    return;
                }
                $content = '';
                $PluginArticleContent = PluginArticleContent::where('article_id', $PluginArticle->id)->find();
                if ($PluginArticleContent) {
                    $content = $PluginArticleContent->content;
                }
                Db::startTrans();
                try {
                    $MessageData = [];
                    if (empty($PluginArticle->push_crowd_uids)) {
                    } else {
                        foreach ($PluginArticle->push_crowd_uids as $key => $value) {
                            $MessageData[] = [
                                'channels_uid' => $PluginArticle->channels_uid,
                                'uid' => $value,
                                'form_id' => $PluginArticle->id,
                                'scene' => $PluginArticle->alias,
                                'state' => State::YES['value'],
                                'title' => $PluginArticle->title,
                                'content' => $content,
                            ];
                        }
                        $PluginArticle->push_state = State::YES['value'];
                        $PluginArticle->last_heartbeat = date('Y-m-d H:i:s');
                        $PluginArticle->push_people_num = count($PluginArticle->push_crowd_uids);
                    }
                    $PluginArticle->save();
                    $PluginNotificationMessage = new PluginNotificationMessage();
                    $PluginNotificationMessage->together(['content'=>['content']])->saveAll($MessageData);
                    Db::commit();
                } catch (\Throwable $th) {
                    Db::rollback();
                    Log::error("JoinPushMessage Process Error: " . $th->getMessage(), $th->getTrace());
                }
            } catch (\Throwable $th) {
                Log::error("JoinPushMessage Process Error: " . $th->getMessage(), $th->getTrace());
            }
        });
    }
}
