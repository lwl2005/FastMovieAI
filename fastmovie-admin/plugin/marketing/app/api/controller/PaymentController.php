<?php

namespace plugin\marketing\app\api\controller;

use app\Basic;
use app\expose\helper\Payment;
use support\Request;

class PaymentController extends Basic
{
    public function index(Request $request)
    {
        $list = Payment::platform($request->channels_uid);
        return $this->resData($list);
    }
}
