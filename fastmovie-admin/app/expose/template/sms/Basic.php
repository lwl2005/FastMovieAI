<?php

namespace app\expose\template\sms;

use app\expose\helper\ConfigGroup;
use app\expose\utils\DataModel;

class Basic extends DataModel
{
    protected $data;
    public function __construct($channels = null)
    {
        if ($channels) {
            $config = new ConfigGroup('sms', '');
            $this->data['channels'] = $channels;
            $this->data['config'] = $config[$channels];
        }
    }
}
