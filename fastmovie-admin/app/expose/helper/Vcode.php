<?php

namespace app\expose\helper;

use app\expose\enum\State;
use app\expose\template\email\Vcode as EmailVcode;
use app\expose\template\sms\Vcode as SmsVcode;
use app\expose\utils\Email;
use app\expose\utils\Sms;
use app\expose\utils\Str;
use support\Log;

class Vcode
{
    public static function check($username, $vcode, $scene, $token = null)
    {
        $request = request();
        if (!empty($token)) {
            $request->sessionId($token);
        }
        $vcodeData = $request->session()->get('vcode:' . $scene . ':' . $username);
        if (empty($vcodeData)) {
            throw new \Exception('验证码不存在');
        }
        if ($vcodeData['vcode'] != $vcode) {
            throw new \Exception('验证码错误');
        }
        if ($vcodeData['expire'] < time()) {
            throw new \Exception('验证码已过期');
        }
        return true;
    }
    public static function send($username, $scene, $token = null)
    {
        $request = request();
        if (!empty($token)) {
            $request->sessionId($token);
        }
        $vcode = Str::random(6, 1);
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $Email = new Email;
            $Email->toemail = $username;
            $Email->setTemplate(EmailVcode::class);
            $Email->setData(['vcode' => $vcode]);
            $Email->send();
        } else {
            $request->session()->set('vcode:' . $scene . ':' . $username, [
                'vcode' => $vcode,
                'expire' => time() + 60 * 5
            ]);
            $config = new ConfigGroup('sms', '');
            foreach ($config['channels'] as $channel) {
                $item = $config[$channel];
                if ($item['enable'] == State::NO['value']) {
                    continue;
                }
                try {
                    if (empty($item['vcode_template_' . $scene])) {
                        continue;
                    }
                    $SmsVcode = new SmsVcode();
                    $SmsVcode->channel = $channel;
                    $SmsVcode->config = $item;
                    $SmsVcode->template_code = $item['vcode_template_' . $scene];
                    $Sms = new Sms;
                    $Sms->mobile = $username;
                    $Sms->setTemplate($SmsVcode);
                    $Sms->setData(['code' => $vcode]);
                    $Sms->send();
                    return true;
                } catch (\Throwable $th) {
                    Log::error("发送短信失败:" . $th->getMessage(), $th->getTrace());
                    continue;
                }
            }
            throw new \Exception('发送失败');
        }
    }
}
