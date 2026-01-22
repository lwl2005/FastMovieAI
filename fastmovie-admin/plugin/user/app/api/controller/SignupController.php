<?php

namespace plugin\user\app\api\controller;

use app\Basic;
use app\expose\enum\State;
use plugin\control\expose\helper\Vcode;
use plugin\user\app\model\PluginUser;
use plugin\user\expose\helper\User;
use plugin\user\utils\enum\VcodeScene;
use support\Request;

class SignupController extends Basic
{
    protected $notNeedLoginAll = true;
    protected $notVerificationSourceAll = true;
    public function index(Request $request)
    {
        $D = $request->post();
        Vcode::check($D['username'],$D['vcode'],VcodeScene::SIGNUP['value'],$D['token']);
        try {
            $UserModel = User::create([
                'mobile' => $D['username'],
            ]);
            $user =PluginUser::where('id',$UserModel->id)->find();
            if(!$user){
                return $this->fail('用户不存在');
            }
            if($user->state == State::NO['value']){
                return $this->fail('用户状态异常');
            }
    
            // 更新登录信息
            $ip = $request->getRealIp();
            $user->login_ip = $ip;
            $user->login_time = date('Y-m-d H:i:s');
            if ($user->save() === false) {
                return $this->fail('登录失败');
            }
            $info = PluginUser::getTokenInfo($user);
            return $this->resData($info);
        } catch (\Throwable $th) {
            return $this->exception($th);
        }
    }
}
