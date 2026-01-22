<?php

namespace plugin\marketing\app\model;

use app\model\Basic;

class PluginMarketingPlan extends Basic
{
    public function price()
    {
        return $this->hasMany(PluginMarketingPlanPrice::class, 'plan_id', 'id');
    }
}
