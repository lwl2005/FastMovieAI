<?php

namespace plugin\control\expose\middleware;

use app\expose\enum\State;
use plugin\control\app\model\PluginChannelsDomain;
use plugin\control\utils\LRUCache;
use support\Redis;
use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

class DomainMapping implements MiddlewareInterface
{
    private static ?LRUCache $cache = null;

    public function __construct()
    {
        if (!self::$cache) {
            self::$cache = new LRUCache(2000);
        }
    }

    public function process(Request $request, callable $next): Response
    {
        $headersKeys=['Authorization','X-ICODE','X-Platform','lang','X-developer','content-type'];
        // 1. 优先 Origin
        if ($origin = $request->header('origin')) {
            $domain = parse_url($origin, PHP_URL_HOST);
        }
        // 2. 再尝试 Referer
        elseif ($referer = $request->header('referer')) {
            $domain = parse_url($referer, PHP_URL_HOST);
        } elseif ($request->host()) {
            $domain = $request->host();
        } else {
            return response('Domain not found', 401);
        }
        $corsHeaders = [
            'Access-Control-Allow-Origin'      => $origin,
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Methods'     => 'GET, POST, PUT, DELETE, OPTIONS, PATCH',
            'Access-Control-Allow-Headers'     => strtolower(implode(',', $headersKeys)),
        ];
        // 1. LRU 进程缓存
        $userId = self::$cache->get($domain);
        if ($userId) {
            $request->channels_uid = $userId;
            $response = $next($request);
            # 允许跨域
            $response->withHeaders($corsHeaders);
            return $response;
        }

        // 2. Redis Hash
        $userId = Redis::hGet('domain_map', $domain);
        if ($userId) {
            self::$cache->set($domain, $userId);
            $request->channels_uid = $userId;
            $response = $next($request);
            # 允许跨域
            $response->withHeaders($corsHeaders);
            return $response;
        }

        // 3. MySQL 兜底
        $PluginChannelsDomain = PluginChannelsDomain::where('domain', $domain)->where('state', State::YES['value'])->find();

        if ($PluginChannelsDomain) {
            Redis::hSet('domain_map', $domain, $PluginChannelsDomain->channels_uid);
            self::$cache->set($domain, $PluginChannelsDomain->channels_uid);
            $request->channels_uid = $PluginChannelsDomain->channels_uid;
            $response = $next($request);
            # 允许跨域
            $response->withHeaders($corsHeaders);
            return $response;
        }
        return response('Domain not found', 401);
    }
}
