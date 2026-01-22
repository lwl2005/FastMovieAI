<?php

namespace plugin\shortplay\app\api\controller;

use app\Basic;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use plugin\notification\expose\helper\Push;
use plugin\shortplay\app\model\PluginShortplayActorCharacterLook;
use plugin\shortplay\app\model\PluginShortplayDrama;
use plugin\shortplay\app\model\PluginShortplayDramaEpisode;
use plugin\shortplay\app\model\PluginShortplayCharacterLook;
use plugin\shortplay\utils\enum\ActorStatus;
use support\Log;
use support\Request;
use Workerman\Coroutine;

class CharacterLookController extends Basic
{
    public function index(Request $request)
    {
        $type = $request->get('type');
        $where = [];
        switch ($type) {
            case 'episode':
                $episode_id = $request->get('episode_id');
                if (!$episode_id) {
                    return $this->fail('分集ID不能为空');
                }
                $PluginShortplayDramaEpisode = PluginShortplayDramaEpisode::where(['id' => $episode_id])->find();
                if (!$PluginShortplayDramaEpisode) {
                    return $this->fail('分集不存在');
                }
                $PluginShortplayDrama = PluginShortplayDrama::where(['id' => $PluginShortplayDramaEpisode->drama_id, 'uid' => $request->uid])->find();
                if (!$PluginShortplayDrama) {
                    return $this->fail('短剧不存在');
                }
                $where[] = ['episode_id', '=', $PluginShortplayDramaEpisode->id];
                break;
            case 'drama':
                $drama_id = $request->get('drama_id');
                if (!$drama_id) {
                    return $this->fail('短剧ID不能为空');
                }
                $PluginShortplayDrama = PluginShortplayDrama::where(['id' => $drama_id, 'uid' => $request->uid])->find();
                if (!$PluginShortplayDrama) {
                    return $this->fail('短剧不存在');
                }
                $where[] = ['drama_id', '=', $PluginShortplayDrama->id];
                break;
            case 'actor':
                $actor_id = $request->get('actor_id');
                if (!$actor_id) {
                    return $this->fail('演员ID不能为空');
                }
                $PluginShortplayActorCharacterLook = PluginShortplayActorCharacterLook::where(['actor_id' => $actor_id, 'uid' => $request->uid])->order('id', 'desc')->with('characterLook')->select()->each(function ($item) {
                    $item->component = 'actor';
                });
                return $this->resData($PluginShortplayActorCharacterLook);
            default:
                $where[] = ['uid', '=', $request->uid];
                break;
        }
        $keywords = $request->get('keywords');
        if ($keywords) {
            $where[] = ['title|overall_style|makeup|hair_style|status_note', 'like', '%' . $keywords . '%'];
        }
        $PluginShortplayCharacterLook = PluginShortplayCharacterLook::where($where)->order('id', 'desc')->select()->each(function ($item) {});
        return $this->resData($PluginShortplayCharacterLook);
    }
    public function update(Request $request)
    {
        $drama_id = $request->post('drama_id');
        if ($drama_id) {
            $PluginShortplayDrama = PluginShortplayDrama::where(['id' => $drama_id, 'uid' => $request->uid])->find();
            if (!$PluginShortplayDrama) {
                return $this->fail('短剧不存在');
            }
        }
        $episode_id = $request->post('episode_id');
        if ($episode_id) {
            $PluginShortplayDramaEpisode = PluginShortplayDramaEpisode::where(['id' => $episode_id, 'drama_id' => $drama_id])->find();
            if (!$PluginShortplayDramaEpisode) {
                return $this->fail('分集不存在');
            }
        }
        $id = $request->post('id');
        if ($id) {
            $PluginShortplayCharacterLook = PluginShortplayCharacterLook::where(['id' => $id, 'uid' => $request->uid])->find();
            if (!$PluginShortplayCharacterLook) {
                return $this->fail('装扮不存在');
            }
        } else {
            $PluginShortplayCharacterLook = new PluginShortplayCharacterLook();
            $PluginShortplayCharacterLook->channels_uid = $request->channels_uid;
            $PluginShortplayCharacterLook->uid = $request->uid;
            if ($drama_id) {
                $PluginShortplayCharacterLook->drama_id = $PluginShortplayDrama->id;
            }
            if ($episode_id) {
                $PluginShortplayCharacterLook->drama_id = $PluginShortplayDramaEpisode->drama_id;
                $PluginShortplayCharacterLook->episode_id = $PluginShortplayDramaEpisode->id;
            }
        }
        $costume_reference_state = (bool)$request->post('costume_reference_state');
        $D = $request->post();
        if (!empty($D['title'])) {
            $PluginShortplayCharacterLook->title = $D['title'];
        }
        if (!empty($D['overall_style'])) {
            $PluginShortplayCharacterLook->overall_style = $D['overall_style'];
        }
        if (!empty($D['makeup'])) {
            $PluginShortplayCharacterLook->makeup = $D['makeup'];
        }
        if (!empty($D['hair_style'])) {
            $PluginShortplayCharacterLook->hair_style = $D['hair_style'];
        }
        if (!empty($D['status_note'])) {
            $PluginShortplayCharacterLook->status_note = $D['status_note'];
        }
        if (!empty($D['costume'])) {
            $PluginShortplayCharacterLook->costume = $D['costume'];
        }
        $PluginShortplayCharacterLook->status = ActorStatus::INITIALIZING['value'];
        if (!empty($D['costume_url'])) {
            $PluginShortplayCharacterLook->costume_url = $D['costume_url'];
            if (!$costume_reference_state) {
                $PluginShortplayCharacterLook->status = ActorStatus::GENERATED['value'];
            }
        }
        $PluginShortplayCharacterLook->save();
        $model_id = $request->post('model_id');
        if ($model_id && $PluginShortplayCharacterLook->status === ActorStatus::INITIALIZING['value']) {
            $data = [
                'uid' => $request->uid,
                'channels_uid' => $request->channels_uid,
                'token' => $request->token,
                'origin' => $request->header('origin'),
                'referer' => $request->header('referer'),
                'host' => $request->host(),
                'form_data' => [
                    'id' => $PluginShortplayCharacterLook->id,
                    'model_id' => $model_id,
                    'costume_url' => $PluginShortplayCharacterLook->costume_url,
                    'costume_reference_state' => $costume_reference_state
                ],
            ];
            Coroutine::create(function () use ($data) {
                try {
                    $client = new Client([
                        'base_uri' => 'http://127.0.0.1:' . getenv('SERVER_PORT'),
                        'verify' => false,
                        'proxy' => false
                    ]);
                    $response = $client->post('/app/shortplay/api/Generate/characterLookCostume', [
                        'json' => $data['form_data'],
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json',
                            'Authorization' => $data['token'],
                            'Origin' => $data['origin'],
                            'Referer' => $data['referer'],
                            'Host' => $data['host'],
                        ],
                    ]);
                    $result = json_decode($response->getBody()->getContents(), true);
                    if (!isset($result['code']) || $result['code'] != 200) {
                        Log::error('初始化装扮失败:' . $result['msg']);
                    } else {
                        Push::send([
                            'uid' => $data['uid'],
                            'channels_uid' => $data['channels_uid'],
                            'event' => 'generatecharacterlookcostume'
                        ], [
                            'id' => $data['form_data']['id'],
                            'status' => ActorStatus::PENDING,
                        ]);
                    }
                } catch (TransferException $e) {
                    Log::error('初始化装扮失败:' . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
                } catch (\Throwable $th) {
                    Log::error('初始化装扮失败:' . $th->getMessage() . PHP_EOL . $th->getTraceAsString());
                }
            });
        }
        return $this->resData($PluginShortplayCharacterLook);
    }
}
