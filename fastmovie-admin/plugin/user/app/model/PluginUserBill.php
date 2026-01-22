<?php

namespace plugin\user\app\model;

use app\model\Basic;
use plugin\control\app\model\PluginChannelsUser;
use plugin\finance\utils\enum\BillScene;
use plugin\user\utils\enum\MoneyAction;
use think\facade\Db;

class PluginUserBill extends Basic
{
    public function channels()
    {
        return $this->hasOne(PluginChannelsUser::class, 'id', 'channels_uid');
    }
    protected function getOptions(): array
    {
        return [
            'type' => [
                'num'  =>  'float',
                'before'  =>  'float',
                'after'  =>  'float',
            ],
        ];
    }
    public static function consume($order)
    {
        $uid = $order->uid;
        $wallet = self::where('uid', $uid)->find();
        if (!$wallet) {
            $wallet = new self();
            $wallet->uid = $uid;
        }
        if ($wallet->balance < $order->money) {
            throw new \Exception('余额不足');
        }
        PluginUserBill::create([
            'uid' => $uid,
            'form_id' => $order->id,
            'num' => $order->money,
            'before' => $wallet->balance,
            'after' => $wallet->balance - $order->money,
            'remarks' => $order->title . ' 订单号:' . $order->trade_no,
            'action' => MoneyAction::DECREASE['value'],
            'scene' => $order->type,
        ]);
        $wallet->balance = Db::raw('balance - ' . $order->money);
        $wallet->balance_used = Db::raw('balance_used + ' . $order->money);
        $wallet->save();
    }
    public static function expend($uid, $money, $title)
    {
        $wallet = self::where('uid', $uid)->find();
        if (!$wallet) {
            $wallet = new self();
            $wallet->uid = $uid;
        }
        PluginUserBill::create([
            'uid' => $uid,
            'num' => $money,
            'before' => $wallet->balance,
            'after' => $wallet->balance - $money,
            'remarks' => $title,
            'action' => MoneyAction::DECREASE['value'],
            'scene' => BillScene::ADMIN['value'],
        ]);
        $wallet->balance = Db::raw('balance - ' . $money);
        $wallet->balance_sum = Db::raw('balance_sum - ' . $money);
        $wallet->save();
    }
    public static function income($uid, $money, $title)
    {
        $wallet = self::where('uid', $uid)->find();
        if (!$wallet) {
            $wallet = new self();
            $wallet->uid = $uid;
        }
        PluginUserBill::create([
            'uid' => $uid,
            'num' => $money,
            'before' => $wallet->balance,
            'after' => $wallet->balance + $money,
            'remarks' => $title,
            'action' => MoneyAction::INCREASE['value'],
            'scene' => BillScene::ADMIN['value'],
        ]);
        $wallet->balance = Db::raw('balance + ' . $money);
        $wallet->balance_sum = Db::raw('balance_sum + ' . $money);
        $wallet->save();
    }
    protected function sceneExternal()
    {
        return $this->visible(['id', 'create_time', 'num', 'before', 'after', 'remarks', 'action', 'scene', 'action_text', 'scene_text']);
    }
}
