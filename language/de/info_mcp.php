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
    'NCZONE_AZTECS' => 'Azteken',
    'NCZONE_BERBERS' => 'Berber',
    'NCZONE_BRITONS' => 'Briten',
    'NCZONE_BURMESE' => 'Burmesen',
    'NCZONE_BYZANTINES' => 'Byzantiner',
    'NCZONE_CELTS' => 'Kelten',
    'NCZONE_CHINESE' => 'Chinesen',
    'NCZONE_ETHOPIANS' => 'Äthiopier',
    'NCZONE_FRANKS' => 'Franken',
    'NCZONE_GOTHS' => 'Goten',
    'NCZONE_HUNS' => 'Hunnen',
    'NCZONE_INCAS' => 'Inka',
    'NCZONE_INDIANS' => 'Inder',
    'NCZONE_ITALIANS' => 'Italiener',
    'NCZONE_JAPANESE' => 'Japaner',
    'NCZONE_KHMER' => 'Khmer',
    'NCZONE_KOREANS' => 'Koreaner',
    'NCZONE_MAGYARS' => 'Ungarn',
    'NCZONE_MALAY' => 'Malaien',
    'NCZONE_MALIANS' => 'Malier',
    'NCZONE_MAYANS' => 'Maya',
    'NCZONE_MONGOLS' => 'Mongolen',
    'NCZONE_PERSIANS' => 'Perser',
    'NCZONE_PORTUGUESE' => 'Portugiesen',
    'NCZONE_SARACENS' => 'Sarazenen',
    'NCZONE_SLAVS' => 'Slaven',
    'NCZONE_SPANISH' => 'Spanier',
    'NCZONE_TEUTONS' => 'Teutonen',
    'NCZONE_TURKS' => 'Türken',
    'NCZONE_VIETNAMESE' => 'Vietnamesen',
    'NCZONE_VIKINGS' => 'Wikinger',
	
	'MCP_SELECT_USER' => 'Benutzer auswählen',
	'MCP_SELECT_USER_HINT' => 'Einen Benutzer auswählen um ihn zu aktivieren oder seine Zone Daten zu ändern.',
	'MCP_PLAYER_RATING' => 'Rating',
	'MCP_PLAYER_RATING_HINT' => 'Rating des Spielers.',
	'MCP_ACTIVATE_PLAYER' => 'Aktivieren',
	'MCP_ACTIVATE_PLAYER_HINT' => 'Erstellt einen Zoneaccount für diesen Spieler.',
	'MCP_USER_NOT_FOUND' => 'Der Benutzername konnte nicht gefunden werden.',

	'MCP_SELECT_CIV' => 'Kultur auswählen',
	'MCP_CIV_HINT' => 'Beachte, dass Kulturen nicht mehr gelöscht werden können!',
	'MCP_CREATE_CIV' => 'Neue Kultur erstellen',
	'MCP_CIV_NAME' => 'Name der Kultur',
	'MCP_CIV_NAME_HINT' => 'Du kannst auch eine Sprachvariable verwenden.',

	'MCP_SELECT_MAP' => 'Karte auswählen',
	'MCP_MAP_HINT' => 'Du kannst auch eine bestehende Karte kopieren.',
	'MCP_CREATE_MAP' => 'Neue Karte erstellen',
	'MCP_MAP_NAME' => 'Kartenname',
	'MCP_MAP_NAME_HINT' => 'Sprachvariablen sind nicht möglich!',
	'MCP_MAP_WEIGHT' => 'Gewichtung der Karte',
	'MCP_MAP_WEIGHT_HINT' => 'Eine Karte mit höherer Gewichtung wird häufiger gelost.',
	'MCP_COPY_MAP' => 'Karte kopieren',
	'MCP_COPY_MAP_HINT' => 'Verwende dies um alle Einstellungen für Kulturen von einer anderen Karte zu übernehmen.',
	'MCP_DONT_COPY' => 'Nicht kopieren',
	'MCP_MAP_CIVS' => 'Einstellungen für Kulturen für diese Karte',
	'MCP_MAP_CIVS_HINT' => 'Der Multiplikator wird beim Losen auf das Spielerrating multipliziert.',
	'MCP_MULTIPLIER' => 'Multiplikator',
	'MCP_FORCE_DRAW' => 'Losen erzwingen',
	'MCP_PREVENT_DRAW' => 'Losen verhindern',
    'MCP_BOTH_TEAMS' => 'Für beide Teams losen',
    
    'MCP_SELECT_MATCH' => 'Spiel ID auswählen',
    'MCP_SELECT_MATCH_HINT' => 'Gebe hier die Spiel ID von dem Spiel ein, welches du editieren möchtest.',
    'MCP_MATCH_WINNER' => 'Sieger auswählen',
    'MCP_MATCH_WINNER_HINT' => 'Wähle hier aus der das Spiel gewonnen hat.',
    'MCP_TEAM1' => 'Team 1',
    'MCP_TEAM2' => 'Team 2',
    'MCP_NO_RESULT' => 'Ohne Wertung',
    'MCP_WRONG_WINNER' => 'Du hast keinen Sieger ausgewählt',
    'MCP_MATCH_REPOSTED' => 'Das Spiel wurde umgepostet',
));
