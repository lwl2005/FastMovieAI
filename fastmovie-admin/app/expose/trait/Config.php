<?php

namespace app\expose\trait;

use app\expose\helper\Config as HelperConfig;
use app\model\Config as ModelConfig;
use app\expose\enum\ResponseEvent;
use app\expose\helper\ConfigGroup;

/**
 * 使用该抽象类需要在控制器中引入以下代码
 * 
 * use app\expose\trait\Config;
 * use app\expose\trait\Json;
 * 
 * Class Config
 * {
 *    use Config;
 *    use Json;
 * }
 */
trait Config
{
    public $channels_uid=null;
    /**
     * 配置管理
     * @return mixed
     */
    private function builder($callback = null)
    {
        $request = request();
        if ($request->method() === 'POST') {
            return $this->update($callback);
        }
        $builder = HelperConfig::formBuilder($request->action, null, $this->channels_uid);
        return $this->resData($builder);
    }
    private function tabsBuilder($callback = null)
    {
        $request = request();
        if ($request->method() === 'POST') {
            return $this->update($callback);
        }
        $builder = ConfigGroup::formBuilder($request->action, null, $this->channels_uid);
        return $this->resData($builder);
    }
    private function update($callback = null)
    {
        $request = request();
        $HelperConfig = new HelperConfig($request->action, null, $this->channels_uid);
        $group = $HelperConfig->getGrop();
        $D = $request->post();
        $where = [];
        $where[] = ['group', '=', $group];
        if ($this->channels_uid) {
            $where[] = ['channels_uid', '=', $this->channels_uid];
        }
        $ConfigModel = ModelConfig::where($where)->find();
        if (!$ConfigModel) {
            $ConfigModel = new ModelConfig;
            $ConfigModel->group = $group;
            if ($this->channels_uid) {
                $ConfigModel->channels_uid = $this->channels_uid;
            }
        }
        $ConfigModel->value = $D;
        if ($ConfigModel->save()) {
            if ($callback) {
                $callback($D);
            }
            return $this->event(ResponseEvent::UPDATE_WEBCONFIG, '保存成功');
        }
        return $this->fail('保存失败');
    }
}
