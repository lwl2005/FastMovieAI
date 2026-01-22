<?php

namespace plugin\shortplay\app\api\controller;

use app\Basic;
use app\expose\enum\State;
use plugin\control\utils\yidevs\Yidevs;
use plugin\model\app\model\PluginModel;
use support\Request;

class VoiceController extends Basic
{
    public function list(Request $request)
    {
        $model_id = $request->get('model_id');
        $scene = $request->get('scene');
        $PluginModel = PluginModel::where(['id' => $model_id, 'scene' => $scene, 'state' => State::YES['value']])->find();
        if (!$PluginModel) {
            return $this->fail('模型不存在');
        }
        $action = $request->get('action', 'yidevs');
        if ($action == 'yidevs') {
            try {
                $result = Yidevs::AudioVoiceList($PluginModel->channels_uid, ['model' => $PluginModel->model_id]);
                return $this->resData($result);
            } catch (\Throwable $th) {
                return $this->exception($th);
            }
        }
    }
}
