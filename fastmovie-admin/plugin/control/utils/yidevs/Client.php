<?php

namespace plugin\control\utils\yidevs;

use app\expose\helper\Config;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use plugin\control\utils\yidevs\stream\Client as StreamClient;
use plugin\control\utils\yidevs\Exception\InvalidResultException;
use plugin\control\utils\yidevs\Exception\InvalidTokenException;
use support\Log;

class Client
{
    protected $token;
    // protected $domain = 'https://api.yidevs.com';
    protected $domain = 'http://110.42.56.227:36999';
    protected $HttpClient;
    protected $channels_uid;
    public function __construct()
    {
        if ($domain = getenv('YIDEVS_DOMAIN')) {
            $this->domain = $domain;
        }
    }
    public function setChannelsUid(int $channels_uid)
    {
        $this->channels_uid = $channels_uid;
        $config = new Config('yidevs', 'control', $channels_uid);
        if (!$config->token) {
            throw new InvalidTokenException('YIDEVS_TOKEN is not set for channels_uid: ' . $channels_uid);
        }
        $this->token = $config->token;
    }
    public function client()
    {
        $this->HttpClient = new GuzzleHttpClient([
            'base_uri' => $this->domain,
            'timeout' => 600,
            'verify' => false,
            'proxy' => false
        ]);
    }
    public function get(string $url, array $query = [])
    {
        try {
            $this->client();
            $response = $this->HttpClient->get($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->token,
                ],
                'query' => $query,
            ]);
            $res = $response->getBody()->getContents();
        } catch (ClientException | RequestException $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            throw new \Exception($e->getMessage());
        } catch (\Throwable $th) {
            Log::error($th->getMessage() . PHP_EOL . $th->getTraceAsString());
            throw new \Exception($th->getMessage());
        }
        $result = json_decode($res, true);
        if (!isset($result['code'])) {
            throw new InvalidResultException($res);
        }
        if ($result['code'] != 200) {
            throw new \Exception($result['msg']);
        }
        return $result['data'];
    }
    public function post(string $url, array $data = [], ?callable $stream = null)
    {
        if ($stream) {
            return new StreamClient($this->token, $this->domain .'/'. $url, $data, $stream);
        }
        try {
            $this->client();
            $response = $this->HttpClient->post($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->token,
                ],
                'json' => $data,
            ]);
            $res = $response->getBody()->getContents();
        } catch (ClientException | RequestException $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            throw new \Exception($e->getMessage());
        } catch (\Throwable $th) {
            Log::error($th->getMessage() . PHP_EOL . $th->getTraceAsString());
            throw new \Exception($th->getMessage());
        }
        $result = json_decode($res, true);
        if (!isset($result['code'])) {
            throw new InvalidResultException($res);
        }
        if ($result['code'] != 200) {
            throw new \Exception($result['msg']);
        }
        return $result['data'];
    }
}
