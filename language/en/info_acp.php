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
	'ACP_ZONE_TITLE'			=> 'nC Zone',

	'ACL_M_ZONE_MANAGE_PLAYERS' => 'nC Zone manage players',
	'ACL_M_ZONE_MANAGE_MAPS' => 'nC Zone manage maps',
	'ACL_M_ZONE_CREATE_MAPS' => 'nC Zone create and delete maps',
	'ACL_M_ZONE_MANAGE_CIVS' => 'nC Zone manage civilizations',
));
