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
    'ACP_CAT_ZONE' => 'nC Zone',
	'ACL_M_ZONE_MANAGE_PLAYERS' => 'Spieler verwalten',
	'ACL_M_ZONE_MANAGE_MAPS' => 'Karten verwalten',
	'ACL_M_ZONE_CREATE_MAPS' => 'Karten erstellen und löschen',
	'ACL_M_ZONE_MANAGE_CIVS' => 'Kulturen verwalten',
	'ACL_M_ZONE_LOGIN_PLAYERS' => 'Andere Spieler einloggen',
	'ACL_M_ZONE_DRAW_MATCH' => 'Losen (auch wenn nicht eingeloggt)',
	'ACL_M_ZONE_CHANGE_MATCH' => 'Andere Spiele ändern',

	'ACL_U_ZONE_VIEW_INFO' => 'Informationen sehen',
	'ACL_U_ZONE_DRAW' => 'Losen',
	'ACL_U_ZONE_CHANGE_MATCH' => 'Spiel ändern',
	'ACL_U_ZONE_LOGIN' => 'Ein-/Ausloggen',
	'ACL_U_ZONE_VIEW_LOGIN' => 'Kann eingeloggte Spieler sehen',
	'ACL_U_ZONE_VIEW_MATCHES' => 'Kann Spiele sehen',
	'ACL_U_ZONE_VIEW_BETS' => 'Kann Wetten sehen',
	'ACL_U_ZONE_BET' => 'Kann Wetten',

	'ACL_A_ZONE_MANAGE_GENERAL' => 'Kann allgemeine Einstellungen verändern',
	'ACL_A_ZONE_MANAGE_DRAW' => 'Kann Loseinstellungen verändern',
));
