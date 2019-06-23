<?php

namespace eru\nczone\utility;

use eru\nczone\config\config;
use eru\nczone\zone\civs;
use eru\nczone\zone\bets;
use eru\nczone\zone\locks;
use eru\nczone\zone\maps;
use eru\nczone\zone\players;
use eru\nczone\zone\matches;
use eru\nczone\zone\draw_teams;
use eru\nczone\zone\draw_settings;
use eru\nczone\zone\misc;

class zone_util
{
    public static function config(): config
    {
        static $config;
        if ($config === null) {
            $config = new config(phpbb_util::config());
        }
        return $config;
    }

    public static function players(): players
    {
        static $players;
        if ($players === null) {
            /** @var players $players */
            $players = phpbb_util::container()->get('eru.nczone.zone.players');
        }
        return $players;
    }

    public static function bets(): bets
    {
        static $bets;
        if ($bets === null) {
            /** @var bets $bets */
            $bets = phpbb_util::container()->get('eru.nczone.zone.bets');
        }
        return $bets;
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

    public static function draw_teams(): draw_teams
    {
        static $draw_teams;
        if ($draw_teams === null) {
            /** @var draw_teams $draw_teams */
            $draw_teams = phpbb_util::container()->get('eru.nczone.zone.draw_teams');
        }
        return $draw_teams;
    }

    public static function draw_settings(): draw_settings
    {
        static $draw_settings;
        if ($draw_settings === null) {
            /** @var draw_settings $draw_settings */
            $draw_settings = phpbb_util::container()->get('eru.nczone.zone.draw_settings');
        }
        return $draw_settings;
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

    public static function misc(): misc
    {
        static $misc;
        if ($misc === null) {
            /** @var misc $misc */
            $misc = phpbb_util::container()->get('eru.nczone.zone.misc');
        }
        return $misc;
    }

    public static function locks(): locks
    {
        static $locks;
        if ($locks === null) {
            /** @var locks $locks */
            $locks = phpbb_util::container()->get('eru.nczone.zone.locks');
        }
        return $locks;
    }
}
