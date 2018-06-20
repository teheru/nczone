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
	'ACP_NCZONE_SAVED' => 'Saved!',

	'NCZONE_DRAW_SETTINGS' => 'Draw settings',
	'NCZONE_DRAW_PLAYER_CIVS' => 'Difference for player civs',
	'NCZONE_DRAW_PLAYER_CIVS_DESCR' => 'Minimum difference of ratings between the player with highest rating and the player with lowest rating in order to draw player civs.',
	'NCZONE_DRAW_TEAM_CIVS' => 'Difference for team civs',
	'NCZONE_DRAW_TEAM_CIVS_DESCR' => 'Minimum difference of ratings of the teams in order to draw team civs.',
	'NCZONE_DRAW_MATCH_EXTRA_CIVS' => 'Extra civs for match civs',
	'NCZONE_DRAW_MATCH_EXTRA_CIVS_DESCR' => 'Number of extra civs to draw for match civs. A higher value results in civs with a more similiar multiplier, but also increases calculation time.',
	'NCZONE_DRAW_TEAM_EXTRA_CIVS' => 'Extra civs for team civs',
	'NCZONE_DRAW_TEAM_EXTRA_CIVS_DESCR' => 'Number of extra civs to draw for team civs. A higher value results in fairer civs in relation to the rating difference of the teams, but also increases calculation time.',
	'NCZONE_DRAW_PLAYER_NUM_CIVS' => 'Number civs for player civs',
	'NCZONE_DRAW_PLAYER_NUM_CIVS_DESCR' => 'Number of civs to draw for player civs. A higher value results in fairer civs in relation to the rating difference of the teams, but also increases calculation time <i>a lot</i>. The value should be at least 1 and at least 2 to enable the mechanism to draw fair(er) civs.',
));
