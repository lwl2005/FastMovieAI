<?php

namespace plugin\user\expose\middleware;

use app\expose\enum\ResponseCode;
use app\expose\trait\Json;
use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
use app\expose\exception\Exception;
use support\Log;

/**
 * 前台应用必须引用此中间件，否者无法验证用户两步验证
 */
class Twofa implements MiddlewareInterface
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
                    Log::error($th->getTraceAsString());
                    throw $th;
                } else {
                    $response = response($th->getMessage(), 500);
                }
            }
        }
        return $response;
    }
    public function Authorization(Request $request)
    {
        # 无控制器地址
        if (!$request->controller) {
            return true;
        }
        # 获取控制器鉴权信息
        $controller = new \ReflectionClass($request->controller);
        $properties = $controller->getDefaultProperties();
        $notNeedLogin = $properties['notNeedLogin'] ?? [];
        $notNeedTwofa = array_merge($notNeedLogin, $properties['notNeedTwofa'] ?? []);
        $notNeedTwofaAll = $properties['notNeedTwofaAll'] ?? false;
        if ($notNeedTwofaAll) {
            $notNeedTwofa = array_merge($notNeedTwofa, [$request->action]);
        }
        # 是否强制登录
        if (in_array($request->action, $notNeedTwofa)) {
            return true;
        }
        if ($request->source === 'saas') {
            return true;
        }
        if (!$request->user) {
            return true;
        }
        if (empty($request->user['twofa_state'])) {
            return true;
        }
        if (empty($request->user['twofa']['expire'])) {
            throw new Exception('需要两步验证', ResponseCode::NEED_TWOFA,['action'=>'logout']);
        }
        if (time() > $request->user['twofa']['expire']) {
            throw new Exception('需要两步验证', ResponseCode::NEED_TWOFA,['action'=>'logout']);
        }
        $request->twofa_state = true;
        $request->twofa = $request->user['twofa'];
    }
}
