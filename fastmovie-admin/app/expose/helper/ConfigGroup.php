<?php

namespace app\expose\helper;

use app\expose\build\builder\FormBuilder;
use app\expose\enum\SubmitEvent;
use app\model\Config as ModelConfig;
use app\expose\utils\DataModel;

class ConfigGroup extends DataModel
{
    protected $configData = [];
    protected $groupData = [];
    protected $data = [];
    protected $group = '';
    protected $channels_uid = null;
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
        $this->configData = config('settings-tabs');
        $this->groupData = $this->configData[$this->group] ?? [];
        $this->builder();
    }
    public function builder()
    {
        $d = [];
        if (!empty($this->groupData)) {
            foreach ($this->groupData as $key => $item) {
                if ($key != 'group') {
                    $d[$item['field']] = $item['value'];
                } else {
                    foreach ($item as $group) {
                        $d[$group['name']] = [];
                        foreach ($group['children'] as $child) {
                            $d[$group['name']][$child['field']] = $child['value'];
                        }
                    }
                }
            }
            $ConfigModel = ModelConfig::where(['group' => $this->group, 'channels_uid' => $this->channels_uid])->find();
            if ($ConfigModel) {
                foreach ($ConfigModel->value as $field => $value) {
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
    public static function get($group, $field = null, $default = null)
    {
        $self = new self($group);
        if ($field) {
            return isset($self->{$field}) ? $self->{$field} : $default;
        }
        return $self->toArray();
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
            'translations' => true,
            'submitEvent' => SubmitEvent::SILENT
        ]);
        $groupData = $self->getGroupData();
        foreach ($groupData as $key => $item) {
            if ($key != 'group') {
                $builder->add($item['field'], $item['title'], $item['component'], $item['value'], $item['extra']);
            }
        }
        foreach ($groupData['group'] as $key => $item) {
            $subBuilder = new FormBuilder($item['name'], $item['title']);
            foreach ($item['children'] as $child) {
                $subBuilder->add($child['field'], $child['title'], $child['component'], $child['value'], $child['extra']);
            }
            $builder->addGroupForm($subBuilder);
        }
        $builder->setData($self->toArray());
        return $builder;
    }
}
