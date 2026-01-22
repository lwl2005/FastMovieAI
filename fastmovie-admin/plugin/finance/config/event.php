<?php

use app\expose\enum\EventName;

return [
    EventName::ORDERS_PAY['value']=>[
        [\plugin\finance\event\Orders::class,'pay']
    ],
    EventName::ORDERS_PAY_SUCCESS['value']=>[
        [\plugin\finance\event\Orders::class,'paySuccess']
    ],
];