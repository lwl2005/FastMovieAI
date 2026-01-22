<?php

namespace plugin\model\app\api\controller;

use app\Basic;
use app\expose\enum\State;
use plugin\model\app\model\PluginModel;

class ModelController extends Basic
{
    protected $notNeedLoginAll = ['models'];
    public function models()
    {
        $PluginModel = PluginModel::where(['state' => State::YES['value']])->order('sort asc')->field('id,channels_uid,icon,name,point,scene,description')->select();
        $models = [];
        foreach ($PluginModel as $model) {
            $models[$model->scene][] = [
                'id' => $model->id,
                'icon' => $model->icon,
                'name' => $model->name,
                'point' => $model->point,
                'scene' => $model->scene,
                'description' => $model->description,
            ];
        }
        return $this->resData($models);
    }
}
