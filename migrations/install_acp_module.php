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

use eru\nczone\config\config;

class install_acp_module extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['nczone_draw_player_civs']);
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v314');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('nczone_rules_post_id', config::default('nczone_rules_post_id'))),
			array('config.add', array('nczone_match_forum_id', config::default('nczone_match_forum_id'))),
			array('config.add', array('nczone_pmatches_page_size', config::default('nczone_pmatches_page_size'))),
			array('config.add', array('nczone_activity_time', config::default('nczone_activity_time'))),
			array('config.add', array('nczone_activity_1', config::default('nczone_activity_1'))),
			array('config.add', array('nczone_activity_2', config::default('nczone_activity_2'))),
			array('config.add', array('nczone_activity_3', config::default('nczone_activity_3'))),
			array('config.add', array('nczone_activity_4', config::default('nczone_activity_4'))),
			array('config.add', array('nczone_activity_5', config::default('nczone_activity_5'))),
			array('config.add', array('nczone_draw_time', config::default('nczone_draw_time'))),
			array('config.add', array('nczone_bet_time', config::default('nczone_bet_time'))),
			array('config.add', array('nczone_points_1vs1', config::default('nczone_points_1vs1'))),
			array('config.add', array('nczone_points_2vs2', config::default('nczone_points_2vs2'))),
			array('config.add', array('nczone_points_3vs3', config::default('nczone_points_3vs3'))),
			array('config.add', array('nczone_points_4vs4', config::default('nczone_points_4vs4'))),
			array('config.add', array('nczone_extra_points', config::default('nczone_extra_points'))),
			array('config.add', array('nczone_info_posts', config::default('nczone_info_posts'))),
			array('config.add', array('nczone_draw_player_civs', config::default('nczone_draw_player_civs'))),
			array('config.add', array('nczone_draw_team_civs', config::default('nczone_draw_team_civs'))),
			array('config.add', array('nczone_draw_match_extra_civs_1vs1', config::default('nczone_draw_match_extra_civs_1vs1'))),
			array('config.add', array('nczone_draw_match_extra_civs_2vs2', config::default('nczone_draw_match_extra_civs_2vs2'))),
			array('config.add', array('nczone_draw_match_extra_civs_3vs3', config::default('nczone_draw_match_extra_civs_3vs3'))),
			array('config.add', array('nczone_draw_match_extra_civs_4vs4', config::default('nczone_draw_match_extra_civs_4vs4'))),
			array('config.add', array('nczone_draw_team_extra_civs_1vs1', config::default('nczone_draw_team_extra_civs_1vs1'))),
			array('config.add', array('nczone_draw_team_extra_civs_2vs2', config::default('nczone_draw_team_extra_civs_2vs2'))),
			array('config.add', array('nczone_draw_team_extra_civs_3vs3', config::default('nczone_draw_team_extra_civs_3vs3'))),
			array('config.add', array('nczone_draw_team_extra_civs_4vs4', config::default('nczone_draw_team_extra_civs_4vs4'))),
			array('config.add', array('nczone_draw_player_num_civs_1vs1', config::default('nczone_draw_player_num_civs_1vs1'))),
			array('config.add', array('nczone_draw_player_num_civs_2vs2', config::default('nczone_draw_player_num_civs_2vs2'))),
			array('config.add', array('nczone_draw_player_num_civs_3vs3', config::default('nczone_draw_player_num_civs_3vs3'))),
			array('config.add', array('nczone_draw_player_num_civs_4vs4', config::default('nczone_draw_player_num_civs_4vs4'))),

			array('module.add', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_NCZONE_TITLE'
			)),
			array('module.add', array(
				'acp',
				'ACP_NCZONE_TITLE',
				array(
					'module_basename'	=> '\eru\nczone\acp\main_module',
					'modes'				=> array('general', 'draw'),
				),
			)),

			array('permission.add', array('a_zone_manage_general', true)), // todo: maybe more fine grained
			array('permission.add', array('a_zone_manage_draw', true)),

			array('permission.role_add', array('nC Zone Admin', 'a_', 'A full administrative role for the nC Zone.')),
			array('permission.permission_set', array('nC Zone Admin', 'a_zone_manage_general')),
			array('permission.permission_set', array('nC Zone Admin', 'a_zone_manage_draw')),
		);
	}
}
