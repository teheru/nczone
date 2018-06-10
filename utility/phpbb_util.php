<?php

namespace eru\nczone\utility;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class phpbb_util
{
    public static function container(): ContainerBuilder
    {
        return $GLOBALS['phpbb_container'];
    }

    public static function request(): \phpbb\request\request
    {
        return $GLOBALS['request'];
    }

    public static function user(): \phpbb\user
    {
        return $GLOBALS['user'];
    }

    public static function template(): \phpbb\template\template
    {
        return $GLOBALS['template'];
    }

    public static function db(): \phpbb\db\driver\driver_interface
    {
        return $GLOBALS['db'];
    }

    public static function root_path(): string
    {
        return $GLOBALS['phpbb_root_path'];
    }

    public static function auth(): \phpbb\auth\auth
    {
        return $GLOBALS['auth'];
    }

    public static function file_url(string $basename): string
    {
        return self::root_path() . $basename . '.' . $GLOBALS['phpEx'];
    }
}
