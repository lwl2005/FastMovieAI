<?php

namespace plugin\user\app\api\controller;

use app\Basic;
use app\expose\enum\ResponseCode;
use app\expose\enum\ResponseEvent;
use app\expose\exception\Exception;
use plugin\control\expose\helper\Wechat;
use plugin\user\app\model\PluginUserWechat;
use support\Redis;
use support\Request;

class BindThirdPartyController extends Basic
{
    public function wechat(Request $request)
    {
        if ($request->twofa_state) {
            $time = time() - (10 * 60);
            if ($request->twofa['time'] < $time) {
                return $this->fail('请验证你的身份', [
                    'code' => ResponseCode::NEED_TWOFA,
                    'action' => 'stop'
                ]);
            }
        }
        if ($request->isWehcatBrowser) {
        } else {
            try {
                $res = Wechat::createQrCode(Wechat::QR_STR_SCENE, 600, [
                    'plugin\user\expose\helper\WechatOfficialAccount',
                    'bind',
                    [
                        'uid' => $request->uid,
                        'channels_uid' => $request->channels_uid,
                    ]
                ]);
                return $this->resData(['qrcode' => $res['url'], 'id' => $res['id'], 'expire' => strtotime("+" . $res['expire_seconds'] . " seconds")]);
            } catch (Exception $e) {
            } catch (\Throwable $th) {
                return $this->exception($th);
            }
            try {
                $res = Wechat::createQrCode(Wechat::QR_SCENE, 600, [
                    'plugin\user\expose\helper\WechatOfficialAccount',
                    'scan',
                    [
                        'uid' => $request->uid,
                        'channels_uid' => $request->channels_uid,
                    ]
                ]);
                return $this->resData(['qrcode' => $res['url'], 'id' => $res['id'], 'expire' => strtotime("+" . $res['expire_seconds'] . " seconds")]);
            } catch (Exception $e) {
            } catch (\Throwable $th) {
                return $this->exception($th);
            }
            /* $auth_url='';
            return $this->code(ResponseCode::OPEN_NEW_WINDOW, '请在微信中打开', [
                'url' => $auth_url
            ]); */
            return $this->fail('无可用的微信服务，请联系客服');
        }
    }
    public function checkWechat(Request $request)
    {
        $PluginUserWechat = PluginUserWechat::where('uid', $request->uid)->find();
        if ($PluginUserWechat) {
            return $this->code(ResponseCode::SUCCESS_EVENT_PUSH, '绑定成功', [
                'event' => ResponseEvent::UPDATE_USERINFO
            ]);
        }
        $id = $request->post('id');
        if(!$id){
            return $this->fail('参数错误');
        }
        $Scene = Redis::get($id);
        if (!$Scene) {
            return $this->fail('二维码已过期');
        }
        $res = Redis::get($id.'.bind');
        if($res){
            $res = json_decode($res,true);
            if($res['status'] == 'fail'){
                return $this->fail($res['message']);
            }
        }
        return $this->code(ResponseCode::WAIT,'等待微信扫码');
    }
    public function unbindWechat(Request $request)
    {
        if ($request->twofa_state) {
            $time = time() - (10 * 60);
            if ($request->twofa['time'] < $time) {
                return $this->fail('请验证你的身份', [
                    'code' => ResponseCode::NEED_TWOFA,
                    'action' => 'stop'
                ]);
            }
        }
        $PluginUserWechat = PluginUserWechat::where('uid', $request->uid)->find();
        if ($PluginUserWechat) {
            $PluginUserWechat->uid=null;
            $PluginUserWechat->save();
        }
        return $this->code(ResponseCode::SUCCESS_EVENT_PUSH, '解绑成功', [
            'event' => ResponseEvent::UPDATE_USERINFO
        ]);
    }
}
