<?php

namespace plugin\notification\app\api\controller;

use app\Basic;
use app\expose\enum\State;
use plugin\developer\utils\enum\InviteState;
use plugin\notification\app\model\PluginNotificationMessage;
use plugin\notification\utils\enum\MessageScene;
use support\Request;

class MessageController extends Basic
{
    public function index(Request $request)
    {
        return $this->resData([
            'unread' => PluginNotificationMessage::where(['uid' => $request->uid, 'read_state' => State::NO['value']])->count(),
            'read' => PluginNotificationMessage::where(['uid' => $request->uid, 'read_state' => State::YES['value']])->count(),
        ]);
    }
    public function list(Request $request)
    {
        $limit = $request->get('limit', 10);
        $where = [];
        $title = $request->get('title');
        if ($title) {
            $where[] = ['title', 'like', "%{$title}%"];
        }
        $read=$request->get('read', 'all');
        if($read !== 'all'){
            $where[] = ['read_state', '=', $read == 'read' ? State::YES['value'] : State::NO['value']];
        }
        $where[] = ['uid', '=', $request->uid];
        $model = PluginNotificationMessage::where($where);
        $list = $model->order('id desc')->paginate($limit)->each(function ($item) {
            if($item->effect=='danger'){
                $item->effect = 'error';
            }
            $item->scene_enum = MessageScene::get($item->scene);
            // switch($item->scene){
            //     case MessageScene::INVITE_JOIN_TEAM['value']:
            //         $item->state_enum = InviteState::get($item->state);
            //         break;
            //     default:
            //         $item->state_enum = State::get($item->state);
            //         if($item->state==State::YES['value']){
            //             $item->effect = 'info';
            //         }
            //         break;
            // }
        });
        return $this->resData($list);
    }

    /**
    * 详情
    * @author:1950781041@qq.com 
    * @Date:2026-01-22
    */
    public function detail(Request $request)
    {
        $id = $request->get('id');
        $message = PluginNotificationMessage::with('content')->where(['id' => $id])->find();
        $message->read_state=1;
        $message->save();
        if($message){
            return $this->resData($message);
        }
        return $this->fail('消息不存在');
    }
}
