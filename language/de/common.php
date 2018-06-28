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
	'NCZONE' => 'nC Zone',
	
	'ACP_NCZONE_TITLE' => 'nC Zone',
	'ACP_NCZONE_GENERAL_TITLE' => 'Allgemeine Einstellungen',
	'ACP_NCZONE_DRAW_TITLE' => 'Loseinstellungen',

	'MCP_ZONE_TITLE'		=> 'nC Zone',
	'MCP_PLAYERS_TITLE'		=> 'Spieler',
	'MCP_CIVS_TITLE'		=> 'Kulturen',
	'MCP_MAPS_TITLE'		=> 'Karten',
    'MCP_MATCHES_TITLE' => 'Spiele',
));
