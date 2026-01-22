<?php

namespace plugin\control\app\api\controller;

use app\Basic;
use app\expose\helper\Captcha;
use app\expose\helper\Config;
use plugin\control\expose\helper\Vcode;
use plugin\shortplay\utils\enum\ActorAge;
use plugin\shortplay\utils\enum\ActorGender;
use plugin\shortplay\utils\enum\ActorSpeciesType;
use plugin\shortplay\utils\enum\ActorStatus;
use plugin\shortplay\utils\enum\StyleClassify;
use plugin\shortplay\utils\enum\VoiceEmotion;
use plugin\shortplay\utils\enum\VoiceLanguage;
use plugin\user\app\model\PluginUser;
use plugin\user\utils\enum\VcodeScene;
use support\Request;

class PublicController extends Basic
{
    protected $notNeedLoginAll = true;
    public function config(Request $request)
    {
        $config = new Config('basic', 'control');
        $config->enum = [
            'actor_species_type' => ActorSpeciesType::getOptions(),
            'actor_gender' => ActorGender::getOptions(),
            'actor_age' => ActorAge::getOptions(),
            'actor_status' => ActorStatus::getOptions(),
            'style_classify' => StyleClassify::getOptions(),
            'voice_emotion' => VoiceEmotion::getOptions(),
            'voice_language' => VoiceLanguage::getOptions(),
        ];
        $pushConfig = new Config('push', 'notification', 0);
        if ($pushConfig->state) {
            $config->push = [
                'url' => $pushConfig->wss_url,
                'app_key' => config('plugin.webman.push.app.app_key'),
                'auth' => config('plugin.webman.push.app.auth'),
            ];
        }
        return $this->resData($config);
    }
    public function getSmsVcode(Request $request)
    {
        $scene = $request->post('scene');
        if (empty($scene)) {
            return $this->fail('参数错误');
        }
        $token = $request->post('token');
        if ($token) {
            $request->sessionId($token);
        }
        try {
            $captcha_state = 1;
            if ($captcha_state) {
                $captcha = $request->post('captcha');
                if (empty($captcha)) {
                    return $this->fail('请输入验证码');
                }
                Captcha::check($captcha, $token);
            }
            $username = $request->post('username');
            switch ($scene) {
                case VcodeScene::LOGIN['value']:
                    $UserModel = PluginUser::where(['mobile' => $username])->find();
                    if (!$UserModel) {
                        throw new \Exception('手机号未注册');
                    }
                    Vcode::send($username, VcodeScene::LOGIN['value'], $token);
                    break;
                case VcodeScene::SIGNUP['value']:
                    Vcode::send($username, VcodeScene::SIGNUP['value'], $token);
                    break;
                case VcodeScene::BIND_MOBILE['value']:
                    if (!$request->uid) {
                        throw new \Exception('请先登录');
                    }
                    // $UserModel = PluginUser::where(['mobile' => $username])->find();
                    // if ($UserModel && $UserModel->id != $request->uid) {
                    //     throw new \Exception('手机号已存在');
                    // }
                    Vcode::send($username, VcodeScene::BIND_MOBILE['value'], $token);
                    break;
                case VcodeScene::BIND_EMAIL['value']:
                    if (!$request->uid) {
                        throw new \Exception('请先登录');
                    }
                    $UserModel = PluginUser::where(['email' => $username])->find();
                    if ($UserModel && $UserModel->id != $request->uid) {
                        throw new \Exception('邮箱已存在');
                    }
                    Vcode::send($username, VcodeScene::BIND_EMAIL['value'], $token);
                    break;
                case VcodeScene::SET_PASSWORD['value']:
                    if (!$request->uid) {
                        throw new \Exception('请先登录');
                    }
                    $mobile = PluginUser::where(['id' => $request->uid])->value('mobile');
                    Vcode::send($mobile, VcodeScene::SET_PASSWORD['value'], $token);
                    break;
                default:
                    throw new \Exception('场景错误');
            }
        } catch (\Throwable $th) {
            return $this->fail($th->getMessage());
        }
        return $this->success();
    }
}
