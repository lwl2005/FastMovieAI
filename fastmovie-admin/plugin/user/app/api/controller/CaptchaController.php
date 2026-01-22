<?php

namespace plugin\user\app\api\controller;

use app\Basic;
use app\expose\enum\State;
use app\expose\helper\Captcha;
use app\expose\utils\Password;
use plugin\user\app\model\PluginUser;
use support\Request;

class CaptchaController extends Basic
{
    protected $notNeedLoginAll = true;
    public function captcha(Request $request)
    {
        $builder = Captcha::create();
        $img_content = $builder->get();
        $request->session()->set('captcha', [
            'captcha' => strtolower($builder->getPhrase()),
            'expire' => time() + 60 * 5
        ]);
        // 输出验证码二进制数据
        return response($img_content, 200, ['Content-Type' => 'image/jpeg']);
    }
    public function captcha_base64(Request $request)
    {
        return Captcha::captcha();
    }
    public function captcha_json(Request $request)
    {
        return $this->resData(Captcha::captchaCode());
    }
}