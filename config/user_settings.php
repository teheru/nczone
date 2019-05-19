<?php

namespace eru\nczone\config;

final class user_settings {
    public const SETTINGS = [
        'view_mchat' => ['default' => '1', 'pattern' => '/^0|1$/'],
        'auto_logout' => ['default' => '0', 'pattern' => '/^[0-9]{1,3}$/']
    ];
}

