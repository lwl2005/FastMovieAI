<?php

namespace plugin\notification\expose\helper;

use app\expose\helper\Config;
use app\expose\helper\Wechat;
use app\expose\utils\Sms;
use plugin\notification\utils\enum\Method;
use plugin\notification\utils\enum\Scene;
use plugin\notification\expose\template\sms\Notice as SmsNotice;
use support\Log;

class Notice
{
    protected $mobiles = [];
    protected $openids = [];
    protected $scene = '';
    protected $data = [];
    protected $method = '';
    public function setMethod($method)
    {
        $this->method = $method;
    }
    public function setScene($scene)
    {
        $this->scene = $scene;
    }
    public function setData($data)
    {
        $this->data = $data;
    }
    public function addMobile($mobile)
    {
        $this->mobiles[] = $mobile;
    }
    public function addOpenid($openid)
    {
        $this->openids[] = $openid;
    }
    public function builderSms()
    {
        $config = new Config('sms_template', 'notification');
        if (isset($config[$this->scene]) && $config[$this->scene]) {
            $sms_template = $config[$this->scene];
            //发送短信
            switch ($this->scene) {
                case Scene::TODO['value']:
                    break;
                case Scene::WARNING['value']:
                    break;
            }
            foreach ($this->data as $key => $value) {
                $sms_template = str_replace('{' . $key . '}', $value, $sms_template);
            }
            foreach ($this->mobiles as $mobile) {
                try {
                    //发送短信
                    $Sms = new Sms;
                    $Sms->mobile = $mobile;
                    $SmsNotice = new SmsNotice;
                    $SmsNotice->setTemplateCode($sms_template);
                    $Sms->setTemplate($SmsNotice);
                    $Sms->setData($this->data);
                    $Sms->send();
                } catch (\Throwable $th) {
                    Log::error("短信通知失败(手机号:mobile):" . $th->getMessage(), $th->getTrace());
                }
            }
        }
    }
    public function builderWechat()
    {
        $config = new Config('wechat_template', 'notification');
        if (isset($config[$this->scene]) && $config[$this->scene]) {
            $template_id = $config[$this->scene];
            //发送微信
            switch ($this->scene) {
                case Scene::TODO['value']:
                    break;
                case Scene::WARNING['value']:
                    $data['template_id'] = $template_id;
                    $data['data'] = [];
                    foreach ($this->data as $key => $value) {
                        $data['data'][$key] = ['value' => $value];
                    }
                    break;
            }
            if (isset($data['template_id'])) {
                foreach ($this->openids as $openid) {
                    $data['touser'] = $openid;
                    try {
                        Wechat::sendTemplate($data);
                    } catch (\Throwable $th) {
                        Log::error("微信通知失败(openid:{$openid}):" . $th->getMessage(), $th->getTrace());
                    }
                }
            }
        }
    }
    public function send()
    {
        $this->mobiles = array_unique($this->mobiles);
        $this->openids = array_unique($this->openids);
        switch ($this->method) {
            case Method::WECHAT_FIRST:
                if (!empty($this->openids)) {
                    try {
                        $this->builderWechat();
                    } catch (\Throwable $th) {
                        Log::error('微信通知发送失败:' . $th->getMessage(), $th->getTrace());
                    }
                } else {
                    try {
                        $this->builderSms();
                    } catch (\Throwable $th) {
                        Log::error('短信通知发送失败:' . $th->getMessage(), $th->getTrace());
                    }
                }
                break;
            case Method::SMS_FIRST:
                if (!empty($this->mobiles)) {
                    try {
                        $this->builderSms();
                    } catch (\Throwable $th) {
                        Log::error('短信通知发送失败:' . $th->getMessage(), $th->getTrace());
                    }
                } else {
                    try {
                        $this->builderWechat();
                    } catch (\Throwable $th) {
                        Log::error('微信通知发送失败:' . $th->getMessage(), $th->getTrace());
                    }
                }
                break;
            case Method::WECHAT_ONLY:
                try {
                    $this->builderWechat();
                } catch (\Throwable $th) {
                    Log::error('微信通知发送失败:' . $th->getMessage(), $th->getTrace());
                }
                break;
            case Method::SMS_ONLY:
                try {
                    $this->builderSms();
                } catch (\Throwable $th) {
                    Log::error('短信通知发送失败:' . $th->getMessage(), $th->getTrace());
                }
                break;
            default:
                try {
                    $this->builderSms();
                } catch (\Throwable $th) {
                    Log::error('短信通知发送失败:' . $th->getMessage(), $th->getTrace());
                }
                try {
                    $this->builderWechat();
                } catch (\Throwable $th) {
                    Log::error('微信通知发送失败:' . $th->getMessage(), $th->getTrace());
                }
                break;
        }
    }
}
