<?php

namespace plugin\model\app\api\controller;

use app\Basic;
use plugin\model\app\model\PluginModelTask;
use plugin\model\utils\enum\ModelTaskStatus;
use support\Request;

class TaskController extends Basic
{
    public function index(Request $request)
    {
        $limit = $request->get('limit', 10);
        $where = [];
        $where[] = ['uid', '=', $request->uid];
        $where[] = ['status', 'in', [ModelTaskStatus::WAIT['value'], ModelTaskStatus::PROCESSING['value'], ModelTaskStatus::WAIT_DOWNLOAD['value'], ModelTaskStatus::DOWNLOADING['value'], ModelTaskStatus::UPLOADING['value'], ModelTaskStatus::SUCCESS['value']]];
        $scene = $request->get('scene', 'all');
        if ($scene != 'all') {
            $where[] = ['scene', '=', $scene];
        }
        $scenes=$request->get('scenes');
        if ($scenes) {
            $where[] = ['scene', 'in', $scenes];
        }
        $model_type = $request->get('model_type', 'all');
        if ($model_type != 'all') {
            $where[] = ['model_type', '=', $model_type];
        }
        $alias_id = $request->get('alias_id');
        if ($alias_id) {
            $where[] = ['alias_id', '=', $alias_id];
        }
        $list = PluginModelTask::where($where)
            ->with(['user' => function ($query) {
                $query->field('id,nickname,headimg,mobile,channels_uid');
            }, 'result' => function ($query) {
                $query->field('task_id,result,image,image_path,video_path,message,channels_uid');
            }])
            ->order('id desc')
            ->paginate($limit);
        return $this->resData($list);
    }
}
