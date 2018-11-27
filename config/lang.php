<?php

namespace eru\nczone\config;

final class lang
{
    public static function en(string $path)
    {
        return self::get('en', $path);
    }

    public static function de(string $path)
    {
        return self::get('de', $path);
    }

    private static function get(string $lang, string $path)
    {
        // todo: add deep path if that is needed
        return self::all()[$lang][$path] ?? [];
    }

    private static function all()
    {
        static $all;
        if ($all === null) {
            $all = \json_decode(\file_get_contents(__DIR__ .'/lang.json'), true);
        }
        return $all;
    }
}
