<?php

namespace app\expose\helper;

use app\expose\build\builder\FormBuilder;
use app\expose\enum\SubmitEvent;
use app\model\Config as ModelConfig;
use app\expose\utils\DataModel;

class Config extends DataModel
{
    protected $configData = [];
    protected $groupData = [];
    protected $data = [];
    protected $group = '';
    protected $channels_uid = null;
    /**
     * 获取配置
     *
     * @param string $group 配置分组，settings目录下的文件名
     * @param string|null $plugin 如若要获取全局配置，请传入''
     */
    public function __construct(string $group, string|null $plugin = null, int|null $channels_uid = null)
    {
        $request = request();
        if ($plugin === null) {
            $plugin = $request->plugin;
        }
        if ($request && $request->channels_uid && $channels_uid === null) {
            $this->channels_uid = $request->channels_uid;
        } elseif ($channels_uid) {
            $this->channels_uid = $channels_uid;
        }
        $this->group = $plugin ? $plugin . '.' . $group : $group;
        $this->configData = config('settings');
        $this->groupData = $this->configData[$this->group] ?? [];
        $this->builder();
    }
    /**
     * 获取当前应用指定配置，如需获取全局配置请new Config('group','');
     *
     * @param string $group 配置分组，settings目录下的文件名
     * @param string|null $field 获取自定字段配置
     * @param mixed $default 默认数据
     * @return array|mixed
     */
    public static function get(string $group, string|null $field = null, mixed $default = null)
    {
        $self = new self($group);
        if ($field) {
            return isset($self->{$field}) ? $self->{$field} : $default;
        }
        return $self->toArray();
    }
    public function builder()
    {
        $d = [];
        $request = request();
        $domain = '';
        $host = '';
        if ($request) {
            $domain = $request->header('x-forwarded-proto') . '://' . $request->host();
            $host = $request->host();
        }
        $replaceData = [
            '{DOMAIN}' => $domain,
            '{HOST}' => $host,
            '{CHANNELS_UID}' => $this->channels_uid
        ];
        if (!empty($this->groupData)) {
            foreach ($this->groupData as $key => $item) {
                $has = false;
                if (is_string($item['value']) && strpos($item['value'], '{DOMAIN}') !== false && $domain) {
                    $has = true;
                } elseif (is_string($item['value']) && strpos($item['value'], '{HOST}') !== false && $host) {
                    $has = true;
                } elseif (is_string($item['value']) && strpos($item['value'], '{CHANNELS_UID}') !== false && $this->channels_uid) {
                    $has = true;
                }
                if ($has) {
                    $d[$item['field']] = str_replace(array_keys($replaceData), array_values($replaceData), $item['value']);
                } else {
                    $d[$item['field']] = $item['value'];
                }
            }
        }
        $ConfigModel = ModelConfig::where(['group' => $this->group, 'channels_uid' => $this->channels_uid])->find();
        if ($ConfigModel) {
            foreach ($ConfigModel->value as $field => $value) {
                $has = false;
                if (is_string($value) && strpos($value, '{DOMAIN}') !== false && $domain) {
                    $has = true;
                } elseif (is_string($value) && strpos($value, '{HOST}') !== false && $host) {
                    $has = true;
                } elseif (is_string($value) && strpos($value, '{CHANNELS_UID}') !== false && $this->channels_uid) {
                    $has = true;
                }
                if ($has) {
                    $d[$field] = str_replace(array_keys($replaceData), array_values($replaceData), $value);
                } else {
                    $d[$field] = $value;
                }
            }
        }
        $this->data = $d;
    }
    public function getGroupData()
    {
        return $this->groupData;
    }
    public function getGrop()
    {
        return $this->group;
    }
    public static function set($group, $field, $value)
    {
        $self = new self($group);
        $self->{$field} = $value;
        $ConfigModel = ModelConfig::where(['group' => $group, 'channels_uid' => $self->channels_uid])->find();
        if (!$ConfigModel) {
            $ConfigModel = new ModelConfig;
            $ConfigModel->group = $group;
            $ConfigModel->channels_uid = $self->channels_uid;
        }
        $ConfigModel->value = $self->toArray();
        $ConfigModel->save();
    }
    public static function formBuilder($group, $plugin = null, $channels_uid = null)
    {
        $self = new self($group, $plugin, $channels_uid);
        $builder = new FormBuilder(null, null, [
            'submitEvent' => SubmitEvent::SILENT
        ]);
        $builder->setTranslations();
        $groupData = $self->getGroupData();
        foreach ($groupData as $key => $item) {
            $builder->add($item['field'], $item['title'], $item['component'], $item['value'], $item['extra']);
        }
        $builder->setData($self->toArray());
        return $builder;
    }
}
