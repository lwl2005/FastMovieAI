<?php

namespace plugin\user\app\api\controller;

use app\Basic;
use plugin\finance\utils\enum\BillScene;
use plugin\user\app\model\PluginUserBill;
use plugin\user\utils\enum\MoneyAction;
use support\Request;

class UserBillController extends Basic
{
    public function index(Request $request)
    {
        $uid = $request->uid;
        $limit=$request->get('limit',10);
        $where=[
            ['uid','=',$uid]
        ];
        $action=$request->get('action','all');
        if($action&&$action!='all'){
            $where[]=['action','=',$action];
        }
        $scene=$request->get('scene','all');
        if($scene&&$scene!='all'){
            $where[]=['scene','=',$scene];
        }
        $start_time=$request->get('start_time');
        $end_time=$request->get('end_time');
        if($start_time&&$end_time){
            $where[]=['create_time','between',[$start_time,$end_time]];
        }elseif($start_time){
            $where[]=['create_time','>=',$start_time];
        }elseif($end_time){
            $where[]=['create_time','<=',$end_time];
        }
        $PluginUserBill = PluginUserBill::where($where)->order('create_time', 'desc')->paginate($limit)->each(function($item){
            $item->action_text=MoneyAction::get($item->action);
            $item->scene_text=BillScene::get($item->scene);
        });
        if($PluginUserBill->isEmpty()){
            return $this->fail();
        }
        return $this->resData($PluginUserBill->scene('external'));
    }
}