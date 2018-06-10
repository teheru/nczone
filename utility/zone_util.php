<?php

namespace eru\nczone\utility;

use eru\nczone\zone\civs;
use eru\nczone\zone\maps;
use eru\nczone\zone\players;

class zone_util
{
    public static function players(): players
    {
        static $players;
        if ($players === null) {
            /** @var players $players */
            $players = phpbb_util::container()->get('eru.nczone.zone.players');
        }
        return $players;
    }

    public static function civs(): civs
    {
        static $civs;
        if ($civs === null) {
            /** @var civs $civs */
            $civs = phpbb_util::container()->get('eru.nczone.zone.civs');
        }
        return $civs;
    }

    public static function maps(): maps
    {
        static $maps;
        if ($maps === null) {
            /** @var maps $maps */
            $maps = phpbb_util::container()->get('eru.nczone.zone.maps');
        }
        return $maps;
    }
}
