<?php

namespace eru\nczone\utility;

use eru\nczone\zone\civs;
use eru\nczone\zone\maps;
use eru\nczone\zone\players;
use eru\nczone\zone\matches;
use eru\nczone\zone\draw;

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

    public static function matches(): matches
    {
        static $matches;
        if ($matches === null) {
            /** @var matches $matches */
            $matches = phpbb_util::container()->get('eru.nczone.zone.matches');
        }
        return $matches;
    }

    public static function draw(): draw
    {
        static $draw;
        if ($draw === null) {
            /** @var draw $draw */
            $draw = phpbb_util::container()->get('eru.nczone.zone.draw');
        }
        return $draw;
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
