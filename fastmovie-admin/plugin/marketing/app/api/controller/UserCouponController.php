<?php

namespace plugin\marketing\app\api\controller;

use app\Basic;
use app\expose\enum\State;
use plugin\finance\utils\enum\OrdersType;
use plugin\marketing\app\model\PluginMarketingCoupon;
use plugin\marketing\app\model\PluginMarketingCouponCode;
use plugin\marketing\utils\enum\UseState;
use plugin\marketing\utils\enum\UseType;
use support\Request;

class UserCouponController extends Basic
{
    public function index(Request $request)
    {
        $uid = $request->uid;
        $now = date('Y-m-d H:i:s');
        $where = [];
        $where[] = ['uid', '=', $uid];
        $action = $request->get('action');
        if ($action) {
            switch ($action) {
                case 'unused':
                    $where[] = ['plugin_marketing_coupon_code.state', '=', UseState::ON['value']];
                    break;
                case 'used':
                    $where[] = ['plugin_marketing_coupon_code.state', 'in', [UseState::USED['value'], UseState::EXPIRE['value']]];
                    break;
            }
        }
        $model = PluginMarketingCouponCode::where($where);
        $server = $request->get('server');
        if ($server) {
            $codeIds = PluginMarketingCouponCode::alias('plugin_marketing_coupon_code')->where($where)->column('coupon_id');
            $codeIds = array_unique($codeIds);
            $ids = PluginMarketingCoupon::alias('a')->whereIn('a.id', $codeIds)
                ->join('plugin_marketing_coupon_server', 'a.id = plugin_marketing_coupon_server.coupon_id', 'left')
                ->where(function ($query) use ($server) {
                    $query->where('plugin_marketing_coupon_server.server', '=', $server);
                })
                ->distinct(true)
                ->field('a.id')
                ->column('a.id');
            $model->whereIn('coupon_id', $ids);
        }

        $coupon = $model->where(function ($query) use ($now) {
            $startTime = [
                ['start_time', '=', NULL],
                ['start_time', '<=', $now]
            ];
            $query->whereOr($startTime);
        })
            ->where(function ($query) use ($now) {
                $endTime = [
                    ['end_time', '=', NULL],
                    ['end_time', '>=', $now]
                ];
                $query->whereOr($endTime);
            })
            ->with(['coupon' => function ($query) {
                $query->field('id,title,coupon_rule,discount,full_price,money,stackable,use_type')->hidden(['id']);
            }])
            ->hasWhere('coupon', ['plugin_marketing_coupon.state' => State::YES['value']])
            ->order('receive_time desc')
            ->select()->scene('external')->each(function ($item) {
                $item->exclusive  = PluginMarketingCoupon::whereIn('id', $item->coupon->exclusiveWith()->column('exclusive_with_id'))->field('id,title')->select()->scene('external');
                $server = $item->coupon->server()->column('server');
                $item->server = OrdersType::getOptions(function ($value) use ($server) {
                    return in_array($value['value'], $server);
                });
                $item->use_type_text = UseType::get($item->coupon->use_type);
                $item->state_text = UseState::get($item->state);
            });
        return $this->resData($coupon);
    }
}
