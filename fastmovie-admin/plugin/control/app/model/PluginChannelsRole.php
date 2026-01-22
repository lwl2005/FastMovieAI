<?php

namespace plugin\control\app\model;

use app\model\Basic;

class PluginChannelsRole extends Basic
{
    public function getRuleAttr($value)
    {
        if (empty($value)) {
            return '';
        }
        return json_decode($value, true);
    }
    public function setRuleAttr($value)
    {
        if (empty($value)) {
            return '';
        }
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }
}
