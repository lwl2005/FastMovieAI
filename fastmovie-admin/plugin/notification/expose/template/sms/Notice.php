<?php

namespace plugin\notification\expose\template\sms;

use app\expose\template\sms\Basic;

class Notice extends Basic
{
    public function setTemplateCode($templateCode)
    {
        $this->data['templateCode'] = $templateCode;
    }
    public function builder()
    {
    }
}
