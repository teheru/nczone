<?php
/**
 *
 * nC Zone. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Marian Cepok, https://new-chapter.eu
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
    'NCZONE_MATCH_MATCH' => 'Match',
    'NCZONE_MATCH_DRAWER' => 'Drawed by',
    'NCZONE_MATCH_MAP' => 'Map',
    'NCZONE_MATCH_CIVS' => 'Civilizations',
    'NCZONE_MATCH_CIVS_BOTH' => 'Both teams',
    'NCZONE_MATCH_CIVS_TEAM1' => 'Team 1',
    'NCZONE_MATCH_CIVS_TEAM2' => 'Team 2',
    'NCZONE_MATCH_TIME_SINCE_DRAW' => 'Time since draw',
    'NCZONE_MATCH_TEAMS' => 'Teams',
    'NCZONE_MATCH_TEAM1' => 'Team 1',
    'NCZONE_MATCH_TEAM2' => 'Team 2',
    'NCZONE_MATCH_VS' => 'VS',
    'NCZONE_MATCH_HAVE_BET' => 'Bets',
));
