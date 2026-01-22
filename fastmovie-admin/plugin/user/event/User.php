<?php

namespace plugin\user\event;

use app\expose\helper\Config;
use plugin\finance\expose\helper\Account;
use plugin\finance\utils\enum\PointsBillScene;
use plugin\user\app\model\PluginUserInvitationCode;

class User
{
    public  function code($event)
    {
        $uid = $event->id;
        $register =  new Config('register', 'user', $event->channels_uid);
        if ($register->register_invitation_code_num) {
            PluginUserInvitationCode::addCode($uid, $register->register_invitation_code_num, $event->channels_uid);
        }
    }
    public  function points($event)
    {
        $uid = $event->id;
        $register =  new Config('register', 'user', $event->channels_uid);
        if ($register->register_give_points) {
            Account::incPoints($uid, $event->channels_uid, $register->register_give_points, PointsBillScene::REGISTER['value'], 0, '注册赠送积分');
        }
    }
}
