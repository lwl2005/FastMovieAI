<?php

namespace plugin\finance\app\api\controller;

use app\Basic;
use plugin\finance\app\model\PluginFinanceWallet;
use support\Request;

class UserController extends Basic
{
    public function wallet(Request $request)
    {
        $uid = $request->uid;
        $PluginFinanceWallet = PluginFinanceWallet::where('uid', $uid)->find();
        if (!$PluginFinanceWallet) {
            $PluginFinanceWallet = new PluginFinanceWallet();
            $PluginFinanceWallet->uid = $uid;
            $PluginFinanceWallet->save();
        }
        return $this->resData($PluginFinanceWallet->hidden(['uid']));
    }
}