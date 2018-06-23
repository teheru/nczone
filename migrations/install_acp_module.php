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
			array('config.add', array('nczone_bet_time', 1200)),
			array('config.add', array('nczone_info_posts', '')),
			array('config.add', array('nczone_draw_player_civs', 600)),
			array('config.add', array('nczone_draw_team_civs', 120)),
			array('config.add', array('nczone_draw_match_extra_civs_1vs1', 7)),
			array('config.add', array('nczone_draw_match_extra_civs_2vs2', 6)),
			array('config.add', array('nczone_draw_match_extra_civs_3vs3', 5)),
			array('config.add', array('nczone_draw_match_extra_civs_4vs4', 4)),
			array('config.add', array('nczone_draw_team_extra_civs_1vs1', 5)),
			array('config.add', array('nczone_draw_team_extra_civs_2vs2', 4)),
			array('config.add', array('nczone_draw_team_extra_civs_3vs3', 3)),
			array('config.add', array('nczone_draw_team_extra_civs_4vs4', 2)),
			array('config.add', array('nczone_draw_player_num_civs_1vs1', 9)),
			array('config.add', array('nczone_draw_player_num_civs_2vs2', 5)),
			array('config.add', array('nczone_draw_player_num_civs_3vs3', 4)),
			array('config.add', array('nczone_draw_player_num_civs_4vs4', 3)),

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
