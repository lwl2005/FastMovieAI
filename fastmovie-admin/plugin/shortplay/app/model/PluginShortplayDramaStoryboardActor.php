<?php

namespace plugin\shortplay\app\model;

use app\model\Basic;
use plugin\control\expose\helper\Uploads;
use plugin\model\app\model\PluginModelTask;
use plugin\model\utils\enum\ModelScene;

class PluginShortplayDramaStoryboardActor extends Basic
{
    protected function getOptions(): array
    {
        return [
            'type' => [
                // 设置JSON字段的类型
                'voice'    =>    'json',
            ]
        ];
    }
    public function actor()
    {
        return $this->hasOne(PluginShortplayActor::class, 'id', 'actor_id');
    }
    public function getThreeViewImageAttr($value, $data)
    {
        return Uploads::url($data['channels_uid'], $value);
    }
    public function setThreeViewImageAttr($value, $data)
    {
        return Uploads::path($data['channels_uid'], $value);
    }
    public function getHeadimgAttr($value, $data)
    {
        return Uploads::url($data['channels_uid'], $value);
    }
    public function setHeadimgAttr($value, $data)
    {
        return Uploads::path($data['channels_uid'], $value);
    }
    public static function onAfterRead($model)
    {

        $model->character_look_state = 0;
        if ($model->character_look_id) {
            $params['drama_id'] = $model->drama_id;
            $params['episode_id'] = $model->episode_id;
            $params['storyboard_id'] = $model->storyboard_id;
            $params['actor_id'] = $model->actor_id;
            $model->character_look_state = PluginShortplayActorCharacterLook::processing($params);
        }
    }
}
