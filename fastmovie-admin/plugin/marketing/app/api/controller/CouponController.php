<?php

namespace plugin\marketing\app\api\controller;

use app\Basic;
use app\expose\enum\State;
use plugin\apps\app\model\PluginApps;
use plugin\finance\app\model\PluginFinanceOrders;
use plugin\finance\utils\enum\OrdersState;
use plugin\finance\utils\enum\OrdersType;
use plugin\marketing\app\model\PluginMarketingCoupon;
use plugin\marketing\app\model\PluginMarketingCouponCode;
use plugin\marketing\utils\enum\ReceiveType;
use plugin\marketing\utils\enum\UseType;
use plugin\user\app\model\PluginUserSiteApps;
use support\Request;
use think\facade\Db;

class CouponController extends Basic
{
    protected $notNeedLogin = ['index'];
    public function index(Request $request)
    {
        $where = [];
        $where[] = ['state', '=', State::YES['value']];
        $where[] = ['show_list', '=', State::YES['value']];
        $model = PluginMarketingCoupon::where($where);
        $server = $request->get('server');
        if ($server) {
            $ids = PluginMarketingCoupon::alias('a')
                ->join('plugin_marketing_coupon_server', 'a.id = plugin_marketing_coupon_server.coupon_id', 'left')
                ->where(function ($query) use ($server) {
                    $query->where('plugin_marketing_coupon_server.server', '=', $server);
                })
                ->distinct(true)
                ->field('a.id')
                ->column('a.id');
            $model->whereIn('id', $ids);
        }

        $uid = $request->uid;
        if ($uid) {
            $hasPaidOrder = PluginFinanceOrders::where(['uid' => $uid])
                ->whereIn('state', PluginMarketingCouponCode::$validOrderStates)
                ->count();
            if ($hasPaidOrder > 0) {
                $model->where('use_type', 'in', [UseType::FIRST_PLUGIN['value'], UseType::UNLIMITED['value']]);
            }
        }
        $data = $model->order('id desc')->select()->scene('external')->each(function ($item) use ($uid) {
            $item->percentage = 0;
            if ($item->sum > 0 && $item->num > 0) {
                $item->percentage = round($item->num / $item->sum * 100, 2);
            }
            $item->receive_type_text = ReceiveType::get($item->receive_type);
            $item->use_type_text = UseType::get($item->use_type);
            $item->receive = 0;
            if ($uid) {
                switch ($item->receive_type) {
                    case ReceiveType::USER_ONLY['value']:
                        if (PluginMarketingCouponCode::where(['uid' => $uid, 'receive_type' => ReceiveType::USER_ONLY['value'], 'coupon_id' => $item->id])->count()) {
                            $item->receive = 1;
                        }
                        break;
                    case ReceiveType::REPEAT_DAY['value']:
                        if (PluginMarketingCouponCode::where(['uid' => $uid, 'coupon_id' => $item->id])->whereDay('receive_time')->count()) {
                            $item->receive = 1;
                        }
                        break;
                    case ReceiveType::REPEAT_WEEK['value']:
                        if (PluginMarketingCouponCode::where(['uid' => $uid, 'coupon_id' => $item->id])->whereWeek('receive_time')->count()) {
                            $item->receive = 1;
                        }
                        break;
                    case ReceiveType::REPEAT_MONTH['value']:
                        if (PluginMarketingCouponCode::where(['uid' => $uid, 'coupon_id' => $item->id])->whereMonth('receive_time')->count()) {
                            $item->receive = 1;
                        }
                        break;
                }
                switch ($item->use_type) {
                    case UseType::FIRST_ORDER['value']:
                        if (PluginFinanceOrders::where(['uid' => $uid, 'state' => OrdersState::FINISH['value']])->count()) {
                            $item->receive = 1;
                        }
                        break;
                }
            }
            if ($item->stackable) {
                $item->exclusive  = PluginMarketingCoupon::whereIn('id', $item->exclusiveWith()->column('exclusive_with_id'))->select()->scene('external');
            } else {
                $item->exclusive = [];
            }
            $server = $item->server()->column('server');
            $item->server = OrdersType::getOptions(function ($value) use ($server) {
                return in_array($value['value'], $server);
            });
        });
        return $this->resData($data);
    }
    public function receive(Request $request)
    {
        $uid = $request->uid;
        $code = $request->post('code');
        # 根据code获取coupon_id
        $coupon_id = PluginMarketingCoupon::codeToId($code);
        Db::startTrans();
        try {
            $coupon = PluginMarketingCoupon::where(['id' => $coupon_id, 'state' => State::YES['value']])->find();
            if (!$coupon) {
                throw new \Exception('优惠券不存在');
            }
            if ($coupon->num <= 0) {
                throw new \Exception('优惠券已领完');
            }
            switch ($coupon->receive_type) {
                case ReceiveType::USER_ONLY['value']:
                    if (PluginMarketingCouponCode::where(['uid' => $uid, 'receive_type' => ReceiveType::USER_ONLY['value']])->count()) {
                        throw new \Exception('已领取过相同类型的优惠券');
                    }
                    break;
                case ReceiveType::REPEAT_DAY['value']:
                    if (PluginMarketingCouponCode::where(['uid' => $uid, 'coupon_id' => $coupon->id])->whereDay('receive_time')->count()) {
                        throw new \Exception('今日已领取过该优惠券');
                    }
                    break;
                case ReceiveType::REPEAT_WEEK['value']:
                    if (PluginMarketingCouponCode::where(['uid' => $uid, 'coupon_id' => $coupon->id])->whereWeek('receive_time')->count()) {
                        throw new \Exception('本周已领取过该优惠券');
                    }
                    break;
                case ReceiveType::REPEAT_MONTH['value']:
                    if (PluginMarketingCouponCode::where(['uid' => $uid, 'coupon_id' => $coupon->id])->whereMonth('receive_time')->count()) {
                        throw new \Exception('本月已领取过该优惠券');
                    }
                    break;
            }
            $couponCode = PluginMarketingCouponCode::where(['coupon_id' => $coupon_id])->whereNull('uid')->find();
            if (!$couponCode) {
                throw new \Exception('优惠券已领完');
            }
            $coupon->num = Db::raw('num-1');
            $coupon->receive_num = Db::raw('receive_num+1');
            $coupon->save();
            $couponCode->uid = $uid;
            $couponCode->expire_time = date('Y-m-d H:i:s', strtotime("+{$couponCode->day} day"));
            if ($couponCode->end_time && $couponCode->end_time < $couponCode->expire_time) {
                $couponCode->expire_time = $couponCode->end_time;
            }
            $couponCode->receive_time = date('Y-m-d H:i:s');
            $couponCode->save();
            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            return $this->exception($th);
        }
        return $this->success('领取成功');
    }
    public function getAvailable(Request $request)
    {
        $uid = $request->uid;
        $now = date('Y-m-d H:i:s');
        $where = [];
        $where[] = ['uid', '=', $uid];
        $where[] = ['plugin_marketing_coupon_code.state', '=', State::NO['value']];
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
        $hasPaidOrder = PluginFinanceOrders::where(['uid' => $request->uid])
            ->whereIn('state', PluginMarketingCouponCode::$validOrderStates)
            ->count();
        if ($hasPaidOrder > 0) {
            $model->where('use_type', 'in', [UseType::FIRST_PLUGIN['value'], UseType::UNLIMITED['value']]);
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
            ->whereTime('expire_time', '>=', $now)
            ->with(['coupon' => function ($query) {
                $query->field('id,title,coupon_rule,discount,full_price,money,stackable')->hidden(['id']);
            }])
            ->hasWhere('coupon', ['plugin_marketing_coupon.state' => State::YES['value']])
            ->order('receive_time desc')
            ->select()->scene('external')->each(function ($item) {
                $item->exclusive  = PluginMarketingCoupon::whereIn('id', $item->coupon->exclusiveWith()->column('exclusive_with_id'))->field('id,title')->select()->scene('external');
                $server = $item->coupon->server()->column('server');
                $item->server = OrdersType::getOptions(function ($value) use ($server) {
                    return in_array($value['value'], $server);
                });
            });
        return $this->resData($coupon);
    }
}
