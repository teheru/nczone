<?php

namespace eru\nczone\utility;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class phpbb_util
{
    public static function container(): ContainerBuilder
    {
        return $GLOBALS['phpbb_container'];
    }

    public static function config(): \phpbb\config\config
    {
        return $GLOBALS['config'];
    }

    public static function request(): \phpbb\request\request
    {
        return $GLOBALS['request'];
    }

    public static function language(): \phpbb\language\language
    {
        return self::container()->get('language');
    }

    public static function template(): \phpbb\template\template
    {
        return $GLOBALS['template'];
    }

    public static function auth(): \phpbb\auth\auth
    {
        return $GLOBALS['auth'];
    }

    public static function file_url(string $basename): string
    {
        return $GLOBALS['phpbb_root_path'] . $basename . '.' . $GLOBALS['phpEx'];
    }

    public static function nczone_path(): string
    {
        return $GLOBALS['phpbb_root_path'] . 'ext/eru/nczone/';
    }
}
