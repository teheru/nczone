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
	'MCP_SELECT_USER' => 'Select user',
	'MCP_SELECT_USER_HINT' => 'Select a user to either activate him or change his zone data.',
	'MCP_PLAYER_RATING' => 'Rating',
	'MCP_PLAYER_RATING_HINT' => 'Rating of the player.',
	'MCP_ACTIVATE_PLAYER' => 'Activate',
	'MCP_ACTIVATE_PLAYER_HINT' => 'Create a new zone account for this player.',
	'MCP_USER_NOT_FOUND' => 'The username could ne be found.',

	'MCP_SELECT_CIV' => 'Select civilization',
	'MCP_CIV_HINT' => 'Note that civilizations cannot be deleted!',
	'MCP_CREATE_CIV' => 'Create new civilization',
	'MCP_CIV_NAME' => 'Civilization name',
	'MCP_CIV_NAME_HINT' => 'You can also use language variables to for civilization names according to the selected language.',

	'MCP_SELECT_MAP' => 'Select map',
	'MCP_MAP_HINT' => 'You can also copy a map from an already existing map.',
	'MCP_CREATE_MAP' => 'Create new map',
	'MCP_MAP_NAME' => 'Map name',
	'MCP_MAP_NAME_HINT' => 'You cannot use language variables here!',
	'MCP_MAP_WEIGHT' => 'Map weight',
	'MCP_MAP_WEIGHT_HINT' => 'A map with a higher weight will be drawn more often.',
	'MCP_COPY_MAP' => 'Copy from map',
	'MCP_COPY_MAP_HINT' => 'Use this to copy all civilization settings from the selected map.',
	'MCP_DONT_COPY' => 'Do not copy',
	'MCP_MAP_CIVS' => 'Map civilization settings',
	'MCP_MAP_CIVS_HINT' => 'The multiplier multiplies the player rating for drawing.',
	'MCP_MULTIPLIER' => 'Multiplier',
	'MCP_FORCE_DRAW' => 'Force drawing',
	'MCP_PREVENT_DRAW' => 'Prevent drawing',
	'MCP_BOTH_TEAMS' => 'Draw for both teams only'
));
