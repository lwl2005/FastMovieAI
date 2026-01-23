<?php

namespace app\controller;

use app\expose\helper\Config;
use support\Request;
use support\Response;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Chunk;
use Workerman\Protocols\Http\ServerSentEvents;
use Workerman\Timer;

class IndexController
{
    public function index(Request $request)
    {
        $config = new Config('basic');
        $templateFile = public_path('template/index.html');
        if (!file_exists($templateFile)) {
            return '请检查 /public/template/index.html 是否存在。';
        }
        return view($templateFile, $config->toArray());
    }
    public function fastmovie(Request $request)
    {
        $config = new Config('basic');
        $templateFile = public_path('template/fastmovie.html');
        if (!file_exists($templateFile)) {
            return '请检查 /public/template/fastmovie.html 是否存在。';
        }
        return view($templateFile, $config->toArray());
    }
    public function test(Request $request)
    {
        //当前连接
        $connect = $request->connection;
        //设置连接头
        $connect->send(new Response(200, [
            'Content-Type' => 'text/event-stream',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Credentials' => 'true',
            'Connection' => 'keep-alive',
        ], "\r\n"));
        $content = file_get_contents(runtime_path('story_stream_project_27_20251111_110715.txt'));
        $streamData = explode("\r\n", $content);
        foreach ($streamData as $line) {
            if (empty($line)) {
                continue;
            }
            $connect->send("id: " . uniqid() . "\n", true);
            $connect->send($line . "\n\n", true);
        }
        $connect->send("data: [DONE]\n\n", true);
        $connect->close();
    }
}
