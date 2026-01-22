<?php

namespace plugin\user\utils\enum;

use app\expose\enum\builder\Enum;

class TotpApp extends Enum
{
    const ONE_PASSWORD = [
        'label' => '1Password',
        'value' => '1password'
    ];
    const GOOGLE = [
        'label' => 'Google Authenticator',
        'value' => 'google'
    ];
    const MICROSOFT = [
        'label' => 'Microsoft Authenticator',
        'value' => 'microsoft'
    ];
    const AUTHY = [
        'label' => 'Authy',
        'value' => 'authy'
    ];
    const DUO = [
        'label' => 'Duo Mobile',
        'value' => 'duo'
    ];
    const LASTPASS = [
        'label' => 'LastPass Authenticator',
        'value' => 'lastpass'
    ];
    const BITWARDEN = [
        'label' => 'Bitwarden Authenticator',
        'value' => 'bitwarden'
    ];
}
