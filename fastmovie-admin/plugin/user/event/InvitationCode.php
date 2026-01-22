<?php

namespace plugin\user\event;

use app\expose\helper\Config;
use plugin\user\app\model\PluginUserInvitationCode;

class InvitationCode
{
    public  function handle($event)
    {
        $uid = $event->id;
        $register =  new Config('register', 'user', $event->channels_uid);
        if ($register->register_invitation_code_num) {
            PluginUserInvitationCode::addCode($uid, $register->register_invitation_code_num,$event->channels_uid    );
        }
    }
}
