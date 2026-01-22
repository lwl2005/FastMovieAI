<?php

namespace plugin\control\expose\helper;

use app\expose\helper\Menus;

class Control extends Menus
{
    /**
     * 构造函数
     *
     * @param object $Install 需实现 getMenus 方法的类返回菜单数据
     */
    public function __construct($Install)
    {
        $request = request();
        $lang = null;
        if ($request && $request->lang) {
            $lang = $request->lang;
        }
        $data = $Install->getMenus();
        if ($lang) {
            $this->translateChildren($data, $request->app, $lang);
        }
        foreach (glob(base_path('plugin/*')) as $path) {
            $plugin_name = basename($path);
            if ($plugin_name == 'control') {
                continue;
            }
            $class = 'plugin\\' . $plugin_name . '\\api\\Control';
            if (!class_exists($class)) {
                continue;
            }
            $plugin = new $class;
            $menus = $plugin->getMenus();
            if ($menus) {
                if ($lang && $plugin->getPlugin()) {
                    $request->plugin = $plugin_name;
                    $this->translateChildren($menus, $request->app, $lang);
                }
                $data[] = $menus;
            }
        }
        $this->builder($data);
    }
}
