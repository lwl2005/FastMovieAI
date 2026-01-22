<?php

namespace plugin\finance\process;

use plugin\finance\app\model\PluginFinanceOrders;
use plugin\finance\app\model\PluginFinanceOrdersLog;
use plugin\finance\utils\enum\OrdersState;
use support\Log;
use Workerman\Crontab\Crontab;
use think\facade\Db;
use Webman\Event\Event;

class Expire
{
    public function onWorkerStart()
    {
        // 每秒钟执行一次
        new Crontab('0 */1 * * * *', function () {
            try {
                $data = PluginFinanceOrders::where(['state' => OrdersState::WAIT_PAY['value']])->whereNotNull('expire_time')->whereTime('expire_time', '<=', date('Y-m-d H:i:s'))->limit(20)->select();
                foreach ($data as $item) {
                    Db::startTrans();
                    try {
                        PluginFinanceOrders::cancel($item);
                        PluginFinanceOrdersLog::warning($item,'订单超时取消');
                        Db::commit();
                    } catch (\Throwable $th) {
                        Db::rollback();
                        Log::error('Process Orders Expire Error:'.$th->getMessage(),$th->getTrace());
                    }
                }
            } catch (\Throwable $th) {
                Log::error('Process Orders Expire Error:'.$th->getMessage(),$th->getTrace());
            }
        });
    }
}
