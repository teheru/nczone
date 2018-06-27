<?php
/**
 *
 * nC Zone. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Marian Cepok, https =>//new-chapter.eu
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
    'NCZONE_AZTECS' => 'Aztecs',
    'NCZONE_BERBERS' => 'Berbers',
    'NCZONE_BRITONS' => 'Britons',
    'NCZONE_BURMESE' => 'Burmese',
    'NCZONE_BYZANTINES' => 'Byzantines',
    'NCZONE_CELTS' => 'Celts',
    'NCZONE_CHINESE' => 'Chinese',
    'NCZONE_ETHOPIANS' => 'Ethopians',
    'NCZONE_FRANKS' => 'Franks',
    'NCZONE_GOTHS' => 'Goths',
    'NCZONE_HUNS' => 'Huns',
    'NCZONE_INCAS' => 'Incas',
    'NCZONE_INDIANS' => 'Indians',
    'NCZONE_ITALIANS' => 'Italians',
    'NCZONE_JAPANESE' => 'Japanese',
    'NCZONE_KHMER' => 'Khmer',
    'NCZONE_KOREANS' => 'Koreans',
    'NCZONE_MAGYARS' => 'Magyars',
    'NCZONE_MALAY' => 'Malay',
    'NCZONE_MALIANS' => 'Malians',
    'NCZONE_MAYANS' => 'Mayans',
    'NCZONE_MONGOLS' => 'Mongols',
    'NCZONE_PERSIANS' => 'Persians',
    'NCZONE_PORTUGUESE' => 'Portuguese',
    'NCZONE_SARACENS' => 'Saracens',
    'NCZONE_SLAVS' => 'Slavs',
    'NCZONE_SPANISH' => 'Spanish',
    'NCZONE_TEUTONS' => 'Teutons',
    'NCZONE_TURKS' => 'Turks',
    'NCZONE_VIETNAMESE' => 'Vietnamese',
    'NCZONE_VIKINGS' => 'Vikings',
	
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
    'MCP_BOTH_TEAMS' => 'Draw for both teams only',
    
    'MCP_MATCHES_TITLE' => 'Matches',
    'MCP_SELECT_MATCH' => 'Select match ID',
    'MCP_SELECT_MATCH_HINT' => 'Enter here the ID of the match you want to edit.',
    'MCP_MATCH_WINNER' => 'Select match winner',
    'MCP_MATCH_WINNER_HINT' => 'Select who (actually) won the match.',
    'MCP_TEAM1' => 'Team 1',
    'MCP_TEAM2' => 'Team 2',
    'MCP_NO_RESULT' => 'No Result',
    'MCP_WRONG_WINNER' => 'You selected a wrong winner',
    'MCP_MATCH_REPOSTED' => 'The match was reposted',
));
