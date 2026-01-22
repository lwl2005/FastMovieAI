<?php

namespace plugin\user\expose\middleware;

use app\expose\enum\ResponseCode;
use app\expose\enum\State;
use app\expose\trait\Json;
use Exception;
use loong\oauth\exception\LockException;
use loong\oauth\exception\TokenExpireException;
use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
use loong\oauth\facade\Auth;
use plugin\user\app\model\PluginUser;

/**
 * 前台应用必须引用此中间件，否者无法获取用户信息
 */
class UserAuth implements MiddlewareInterface
{
    use Json;
    public function process(Request $request, callable $next): Response
    {
        if ($request->method() == 'OPTIONS') {
            return response('', 204);
        }
        // 鉴权检测
        try {
            $this->Authorization($request);
            $response = $next($request);
        } catch (\Throwable $th) {
            if ($request->expectsJson()) {
                $response = $this->exception($th);
            } else {
                if (config('app.debug')) {
                    throw $th;
                } else {
                    $response = response($th->getMessage(), 500);
                }
            }
        }
        return $response;
    }

    /**
     * 业务逻辑
     * @author 贵州猿创科技有限公司
     * @Email unknown.renlong@gmail.com
     * @DateTime 2023-03-11
     */
    public function Authorization(Request $request)
    {
        # 无控制器地址
        if (!$request->controller) {
            return true;
        }
        # 获取控制器鉴权信息
        $controller = new \ReflectionClass($request->controller);
        $properties = $controller->getDefaultProperties();
        # 无需登录方法
        $notNeedLogin = $properties['notNeedLogin'] ?? [];
        # 是否整个控制器都不需要登录
        $notNeedLoginAll = $properties['notNeedLoginAll'] ?? false;
        if ($notNeedLoginAll) {
            $notNeedLogin = array_merge($notNeedLogin, [$request->action]);
        }
        # 是否强制登录
        $isForceLogin = true;
        if (in_array($request->action, $notNeedLogin)) {
            $isForceLogin = false;
        }
        try {
            # 令牌验证
            $token = $request->header('authorization');
            if (!$token) {
                throw new Exception('请先登录', ResponseCode::NEED_LOGIN);
            }
            $user = Auth::setPrefix('CONTROL')->decrypt($token);
            if (!$user) {
                throw new TokenExpireException();
            }
            $request->token         = $token;
            $request->user          = $user;
            $request->channels_uid  = $user['channels_uid'];
            $request->uid           = $user['uid'];
        } catch (TokenExpireException $e) {
            if ($isForceLogin) {
                throw new Exception('登录已过期，请重新登录', ResponseCode::NEED_LOGIN);
            }
        } catch (LockException $e) {
            if ($isForceLogin) {
                throw new Exception($e->getMessage(), ResponseCode::LOCK);
            }
        } catch (\Throwable $th) {
            if ($isForceLogin) {
                throw new \Exception($th->getMessage(), ResponseCode::NEED_LOGIN);
            }
        }
        if ($request->uid) {
            $User = PluginUser::where('id', $request->uid)->find();
            if ($User->state != State::YES['value']) {
                throw new Exception("您的账号已被禁用", ResponseCode::NEED_LOGIN);
            }
        }
    }
}
