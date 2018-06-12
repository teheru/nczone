<?php
/**
 *
 * nC Zone. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Marian Cepok, https://new-chapter.eu
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eru\nczone\migrations;

class install_db_create extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v314');
	}

	public function update_schema()
	{
		return array(
			'add_tables' => array(
				$this->table_prefix . 'zone_players' => array(
					'COLUMNS' => array(
						'user_id' => array('UINT', null),
						'rating' => array('UINT', 0),
						'logged_in' => array('TIMESTAMP', 0),
						'matches_won' => array('UINT', 0),
						'matches_loss' => array('UINT', 0)
					),
					'PRIMARY_KEY' => 'user_id'
				),
				$this->table_prefix . 'zone_matches' => array(
					'COLUMNS' => array(
						'match_id' => array('UINT', null, 'auto_increment'),
						'team1_id' => array('UINT', 0),
						'team2_id' => array('UINT', 0),
						'draw_user_id' => array('UINT', 0),
						'post_user_id' => array('UINT', 0),
						'draw_time' => array('TIMESTAMP', 0),
						'post_time' => array('TIMESTAMP', 0),
						'winner_team_id' => array('UINT:2', 0)
					),
					'PRIMARY_KEY' => 'match_id',
					'KEYS' => array(
						'team1' => array('INDEX', 'team1_id'),
						'team2' => array('INDEX', 'team2_id'),
						'draw_uid' => array('INDEX', 'draw_user_id'),
						'post_uid' => array('INDEX', 'post_user_id')
					)
				),
				$this->table_prefix . 'zone_match_players' => array(
					'COLUMNS' => array(
						'team_id' => array('UINT', 0),
						'user_id' => array('UINT', 0),
						'draw_rating' => array('UINT', 0)
					),
					'PRIMARY_KEY' => array('team_id', 'user_id')
				),
				$this->table_prefix . 'zone_civs' => array(
					'COLUMNS' => array(
						'civ_id' => array('UINT', NULL, 'auto_increment'),
						'civ_name' => array('VCHAR:48', '')
					),
					'PRIMARY_KEY' => 'civ_id'
				),
				$this->table_prefix . 'zone_maps' => array(
					'COLUMNS' => array(
						'map_id' => array('UINT', NULL, 'auto_increment'),
						'map_name' => array('VCHAR:48', ''),
						'weight' => array('DECIMAL', 0.0),
					),
					'PRIMARY_KEY' => 'map_id'
				),
				$this->table_prefix . 'zone_map_civs' => array(
					'COLUMNS' => array(
						'map_id' => array('UINT', 0),
						'civ_id' => array('UINT', 0),
						'multiplier' => array('DECIMAL', 0.0),
						'force_draw' => array('BOOL', 0),
						'prevent_draw' => array('BOOL', 0),
						'both_teams' => array('BOOL', 0)
					),
					'PRIMARY_KEY' => array('map_id', 'civ_id')
				),
				$this->table_prefix . 'zone_match_civs' => array(
					'COLUMNS' => array(
						'match_id' => array('UINT', 0),
						'civ_id' => array('UINT', 0),
						'number' => array('UINT:3', 0)
					),
					'PRIMARY_KEY' => array('match_id', 'civ_id')
				),
				$this->table_prefix . 'zone_match_team_civs' => array(
					'COLUMNS' => array(
						'team_id' => array('UINT', 0),
						'civ_id' => array('UINT', 0),
						'number' => array('UINT:3', 0)
					),
					'PRIMARY_KEY' => array('team_id', 'civ_id')
				),
				$this->table_prefix . 'zone_match_player_civs' => array(
					'COLUMNS' => array(
						'user_id' => array('UINT', 0),
						'civ_id' => array('UINT', 0)
					),
					'PRIMARY_KEY' => array('user_id', 'civ_id')
				),
				$this->table_prefix . 'zone_player_map' => array(
					'COLUMNS' => array(
						'user_id' => array('UINT', 0),
						'map_id' => array('UINT', 0),
						'time' => array('TIMESTAMP', 0)
					),
					'PRIMARY_KEY' => array('user_id', 'map_id')
				),
				$this->table_prefix . 'zone_player_civ' => array(
					'COLUMNS' => array(
						'user_id' => array('UINT', 0),
						'civ_id' => array('UINT', 0),
						'time' => array('TIMESTAMP', 0)
					),
					'PRIMARY_KEY' => array('user_id', 'civ_id')
				)
			)
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables'	=> array(
				$this->table_prefix . 'zone_players',
				$this->table_prefix . 'zone_matches',
				$this->table_prefix . 'zone_match_players',
				$this->table_prefix . 'zone_civs',
				$this->table_prefix . 'zone_maps',
				$this->table_prefix . 'zone_map_civs',
				$this->table_prefix . 'zone_match_civs',
				$this->table_prefix . 'zone_match_team_civs',
				$this->table_prefix . 'zone_match_player_civs',
				$this->table_prefix . 'zone_player_map',
				$this->table_prefix . 'zone_player_civ'
			),
		);
	}
}
