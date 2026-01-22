<?php

namespace plugin\user\utils\enum;

use app\expose\enum\builder\Enum;

class CertificateType extends Enum
{
    const ID_CARD = [
        'label' => '身份证',
        'value' => 'idcard',
    ];
    const PASSPORT = [
        'label' => '护照',
        'value' => 'passport',
    ];
    const HK_MC_PASSPORT = [
        'label' => '港澳通行证',
        'value' => 'hk_mc_passport',
    ];
    const TW_PASSPORT = [
        'label' => '台湾通行证',
        'value' => 'tw_passport',
    ];
    const TW_RETURN_PASSPORT = [
        'label' => '台胞证',
        'value' => 'tw_return_passport',
    ];
    const HK_MC_RETURN_PASSPORT = [
        'label' => '返乡证',
        'value' => 'hk_mc_return_passport',
    ];
}