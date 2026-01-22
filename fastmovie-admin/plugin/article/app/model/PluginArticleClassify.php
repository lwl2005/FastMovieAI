<?php

namespace plugin\article\app\model;

use app\model\Basic;

class PluginArticleClassify extends Basic
{
    public static function options($pid=null)
    {
        if ($pid) {
            $data = self::where(['pid' => $pid])->select();
        } else {
            $data = self::whereNull('pid')->select();
        }
        $options = [];
        if ($data->isEmpty()) {
            return $options;
        }
        foreach ($data as $item) {
            $temp = [
                'value' => $item->id,
                'label' => $item->title
            ];
            $children = self::options($item->id);
            if (!empty($children)) {
                $temp['children'] = $children;
            }
            $options[] = $temp;
        }
        return $options;
    }
}