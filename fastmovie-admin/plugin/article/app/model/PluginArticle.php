<?php

namespace plugin\article\app\model;

use app\model\Basic;
use think\model\concern\SoftDelete;

class PluginArticle extends Basic
{
    use SoftDelete;
    protected function getOptions(): array
    {
        return [
            'type' => [
                // 设置JSON字段的类型
                'thumb'    =>    'json',
                'push_crowd_uids'    =>    'json'
            ]
        ];
    }

    public function content()
    {
        return $this->hasOne(PluginArticleContent::class, 'article_id', 'id');
    }

    // public function getPushCrowdUidsAttr($value)
    // {
    //     if (empty($value)) {
    //         return  '';
    //     }
    //     return json_decode($value, true);
    // }

    // public function setPushCrowdUidsAttr($value)
    // {
    //     if (empty($value)) {
    //         return '';
    //     }
    //     return json_encode($value, JSON_UNESCAPED_UNICODE);
    // }
}
