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
	'ACL_M_ZONE_MANAGE_PLAYERS' => 'manage players',
	'ACL_M_ZONE_MANAGE_MAPS' => 'manage maps',
	'ACL_M_ZONE_CREATE_MAPS' => 'create and delete maps',
	'ACL_M_ZONE_MANAGE_CIVS' => 'manage civilizations',

	'ACL_U_ZONE_DRAW' => 'draw matches',
	'ACL_U_ZONE_LOGIN' => 'login / logout',
	'ACL_U_ZONE_VIEW_LOGIN' => 'can see logged in players',
	'ACL_U_ZONE_VIEW_MATCHES' => 'can see matches',
));
