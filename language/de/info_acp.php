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
	'ACP_NCZONE_SAVED' => 'Gespeichert!',

	'NCZONE_RULES_POST_ID' => 'Regeln Post ID',
	'NCZONE_RULES_POST_ID_DESCR' => 'Die ID des Posts welcher auf der Regeln Seite angezeigt werden soll.',
	'NCZONE_MATCH_FORUM_ID' => 'Post Forum ID',
	'NCZONE_MATCH_FORUM_ID_DESCR' => 'Die ID des Forums in welches die Spielergebnisse gepostet werden sollen.',
	'NCZONE_PMATCHES_PAGE_SIZE' => 'Vergangene Spiele Seitenlänge',
	'NCZONE_PMATCHES_PAGE_SIZE_DESCR' => 'Anzahl der Spiele die auf bei \'Vergangene Spiele\' pro Seite angezeigt werden sollen.',
	'NCZONE_ACTIVITY_TIME' => 'Aktivitätszeitraum',
	'NCZONE_ACTIVITY_TIME_DESCR' => 'Der Zeitraum (in Tagen) in welchem Aktivitätsspiele gezählt werden sollen.',
	'NCZONE_ACTIVITY' => 'Aktivität',
	'NCZONE_ACTIVITY_DESCR' => 'Anzahl an Spielen die im Aktivitätszeitraum gespielt werden müssen um Aktivität 1/2/3/4/5 zu erreichen.',
	'NCZONE_DRAW_TIME' => 'Loszeit',
	'NCZONE_DRAW_TIME_DESCR' => 'Die Zeit die ein Spieler hat um eine Losung zu bestätigen.',
	'NCZONE_BET_TIME' => 'Wettzeit',
	'NCZONE_BET_TIME_DESCR' => 'Die Zeit (in Sekunden) die eine Wette vor Ende eines Spiels abgegeben werden muss damit sie gezählt werden.',
	'NCZONE_INFO_POSTS' => 'Informations Post IDs',
	'NCZONE_INFO_POSTS_DESCR' => 'IDs der Posts welche in \'Max wichtige Informationen\' angezeigt werden sollen.',

	'NCZONE_DRAW_SETTINGS' => 'Loseinstellungen',
	'NCZONE_DRAW_PLAYER_CIVS' => 'Differenz für Spielercivs',
	'NCZONE_DRAW_PLAYER_CIVS_DESCR' => 'Minimale Differenz zwischen den Ratings des niedrigsten und höchsten Spieler einer Losung, damit Spielercivs gelost werden.',
	'NCZONE_DRAW_TEAM_CIVS' => 'DIfferenz für Teamcivs',
	'NCZONE_DRAW_TEAM_CIVS_DESCR' => 'Minimale Differenz der Ratings zweier Teams damit Teamcivs gelost werden.',
	'NCZONE_DRAW_MATCH_EXTRA_CIVS' => 'Extra Kulturen für Spielcivs',
	'NCZONE_DRAW_MATCH_EXTRA_CIVS_DESCR' => 'Anzahl an extra civs bei Spielcivs. Ein höherer Wert liefert Kulturen mit ähnlicheren Multiplikatoren, erhöht allerdings auch die Rechenzeit. 1vs1 / 2vs2 / 3vs3 / 4vs4',
	'NCZONE_DRAW_TEAM_EXTRA_CIVS' => 'Extra Kulturen für Teamcivs',
	'NCZONE_DRAW_TEAM_EXTRA_CIVS_DESCR' => 'Anzahl an Extra Kulturen für Teamcivs. Ein höherer Wert liefert fairere Kulturen um Ratingdifferenzen auszugleichen, erhöht allerdings auch die Rechenzeit. 1vs1 / 2vs2 / 3vs3 / 4vs4',
	'NCZONE_DRAW_PLAYER_NUM_CIVS' => 'Anzahl an Kulturen für Spielercivs',
	'NCZONE_DRAW_PLAYER_NUM_CIVS_DESCR' => 'Anzahl an Kulturen für Spielercivs. Ein höherer Wert sorgt für fairere Kulturen im Bezug auf Ratingunterschiede im Spiel, erhöht allerdings auch die Rechenzeit <i>sehr stark</i>. 1vs1 / 2vs2 / 3vs3 / 4vs4',
));
