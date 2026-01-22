<?php

namespace plugin\control\utils\yidevs\stream;

use plugin\control\utils\yidevs\Exception\HttpCurlException;
use support\Log;

class Client
{
    protected $streamFunction;
    public function __construct(string $token, string $url, array $data = [], ?callable $streamFunction = null)
    {
        try {
            $this->streamFunction = $streamFunction;
            $data['stream'] = true;
            $curl_info = [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 600,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => [
                    'Content-Type:application/json',
                    'Accept:application/json',
                    'Authorization:Bearer ' . $token,
                ],
                CURLOPT_PROXY => false
            ];

            $curl_info[CURLOPT_WRITEFUNCTION] = [$this, 'streamWriteFunction'];

            $curl = curl_init();

            curl_setopt_array($curl, $curl_info);
            $response = curl_exec($curl);
            // $info = curl_getinfo($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if (! $response) {
                throw new HttpCurlException($error);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function streamWriteFunction($ch, $data)
    {
        // data 必须是字符串
        if (!is_string($data)) {
            return strlen($data);
        }

        try {
            // 用户处理
            $this->parseResponse($data, function ($line) use ($ch) {
                if (is_callable($this->streamFunction)) {
                    $line = ($this->streamFunction)($ch, $line);
                }

                if (is_string($line)) {
                    // 检查 DONE
                    if ($line === 'DONE') {
                        throw new \Exception('DONE');
                    }
                } else {
                    if (isset($line['error'])) {
                        throw new \Exception('DONE');
                    }
                }
            });
        } catch (\Throwable $th) {
            if ($th->getMessage() !== 'DONE') {
                Log::error($th->getMessage() . PHP_EOL . $th->getTraceAsString());
            }
        }
        flush();

        // 必须返回 exact byte count
        return strlen($data);
    }
    public function parseResponse($data, callable $callback)
    {
        // 按换行分割，兼容 \n 和 \r\n
        $lines = preg_split("/\r?\n/", $data);

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue; // 跳过空行
            }

            // 必须以 data: 开头
            if (strpos($line, 'data: ') === 0) {
                // 截掉 data: 前缀
                $json = trim(substr($line, 6));
            } else {
                $json = $line;
            }
            // SSE 结束标记：[DONE]
            if ($json === '[DONE]') {
                $callback('DONE');
                continue;
            }
            try {
                // 尝试解析 JSON
                $payload = json_decode($json, true);
                if (isset($payload['error'])) {
                    throw new \Exception("[{$payload['error']['code']}]" . $payload['error']['message']);
                }
                $line = $payload;
            } catch (\Throwable $th) {
            }
            $callback($line);
        }
    }
}
