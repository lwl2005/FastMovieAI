<?php

namespace plugin\control\app\model;

use app\model\Basic;

class PluginChannelsWechat extends Basic
{
    public static function onBeforeWrite($model)
    {
        if(!empty($model->nickname)){
            $model->nickname= iconv('UTF-8', 'UTF-8//IGNORE', $model->nickname);
        }
    }
}
