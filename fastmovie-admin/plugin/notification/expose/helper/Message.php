<?php

namespace plugin\notification\expose\helper;

use app\expose\enum\State;
use app\expose\helper\Config;
use plugin\notification\app\model\PluginNotificationMessage;
use plugin\notification\app\model\PluginNotificationMessageContent;
use plugin\notification\utils\enum\MessageScene;
use support\Log;

class Message
{
    protected $uid = null;
    protected $channels_uid = null;
    protected $form_uid = null;
    protected $form_id = null;
    protected $scene = null;
    protected $alias_id = null;
    protected $extra = [];
    protected $title = null;
    protected $effect = 'info';
    protected $content = null;
    protected $subtitle = null;
    public function setChannelsUid(int $channels_uid)
    {
        $this->channels_uid = $channels_uid;
        return $this;
    }
    public function setUid(int $uid)
    {
        $this->uid = $uid;
        return $this;
    }
    public function setFormUid(int $form_uid)
    {
        $this->form_uid = $form_uid;
        return $this;
    }
    public function setFormId(int $form_id)
    {
        $this->form_id = $form_id;
        return $this;
    }
    public function setScene(string $scene)
    {
        $this->scene = $scene;
        return $this;
    }
    public function setAliasId(int $alias_id)
    {
        $this->alias_id = $alias_id;
        return $this;
    }
    public function setExtra(array $extra)
    {
        $this->extra = $extra;
        return $this;
    }
    public function setTitle(string $title)
    {
        $this->title = $title;
        return $this;
    }
    public function setEffect(string $effect)
    {
        $this->effect = $effect;
        return $this;
    }
    public function setContent(string $content)
    {
        $this->content = $content;
        return $this;
    }
    public function setSubtitle(string $subtitle)
    {
        $this->subtitle = $subtitle;
        return $this;
    }
    public function save()
    {
        $messageId = null;
        if ($this->uid) {
            $Message = new PluginNotificationMessage();
            $Message->channels_uid = $this->channels_uid;
            $Message->uid = $this->uid;
            $Message->form_uid = $this->form_uid;
            $Message->form_id = $this->form_id;
            $Message->scene = $this->scene;
            $Message->alias_id = $this->alias_id;
            $Message->extra = $this->extra;
            $Message->title = $this->title;
            $Message->subtitle = $this->subtitle;
            $Message->effect = $this->effect;
            $Message->save();
            $MessageContent = new PluginNotificationMessageContent();
            $MessageContent->message_id = $Message->id;
            $MessageContent->content = $this->content;
            $MessageContent->save();
            $messageId = $Message->id;
        }

        $pushConfig = new Config('push', 'notification');
        if ($pushConfig->state) {
            try {
                if ($this->uid) {
                    Push::send(['uid' => $this->uid, 'event' => 'notify','channels_uid' => $this->channels_uid],  [
                        'unread' => PluginNotificationMessage::where(['uid' => $this->uid, 'read_state' => State::NO['value'], 'channels_uid' => $this->channels_uid])->count(),
                        'read'  => PluginNotificationMessage::where(['uid' => $this->uid, 'read_state' => State::YES['value'], 'channels_uid' => $this->channels_uid])->count(),
                        'list' => [
                            [
                                'id' => $messageId,
                                'scene' => MessageScene::get($this->scene),
                                'title' => $this->title,
                            ]
                        ]
                    ]);
                } else {
                    Push::trigger('notify', 'message', [
                        'id' => $messageId,
                        'scene' => MessageScene::get($this->scene),
                        'title' => $this->title,
                        'content' => $this->content,
                        'extra' => $this->extra,
                        'effect' => $this->effect,
                        'alias_id'=>$this->alias_id,
                    ]);
                }
            } catch (\Throwable $th) {
                Log::error('Message save push error: ' . $th->getMessage(), $th->getTrace());
            }
        }
    }
}
