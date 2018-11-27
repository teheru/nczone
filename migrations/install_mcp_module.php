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

class install_mcp_module extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		$sql = 'SELECT module_id
			FROM ' . $this->table_prefix . "modules
			WHERE module_class = 'mcp'
				AND module_langname = 'MCP_ZONE_TITLE'";
		$result = $this->db->sql_query($sql);
		$module_id = $this->db->sql_fetchfield('module_id');
		$this->db->sql_freeresult($result);

		return $module_id !== false;
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v314');
	}

	public function update_data()
	{
		return array(
			array('module.add', array(
				'mcp',
				0,
				'MCP_ZONE_TITLE'
			)),
			array('module.add', array(
				'mcp',
				'MCP_ZONE_TITLE',
				array(
					'module_basename'	=> '\eru\nczone\mcp\main_module',
					'modes'				=> array('players', 'civs', 'maps'),
				),
			)),

			array('permission.add', array('m_zone_manage_players', true)),
			array('permission.add', array('m_zone_manage_civs', true)),
			array('permission.add', array('m_zone_manage_maps', true)),
			array('permission.add', array('m_zone_create_maps', true)),
			array('permission.add', array('m_zone_login_players', true)),
			array('permission.add', array('m_zone_draw_match', true)),
			array('permission.add', array('m_zone_change_match', true)),

			array('permission.role_add', array('nC Zone Mod', 'm_', 'A moderator role for the nC Zone.')),
			array('permission.permission_set', array('nC Zone Mod', 'm_zone_manage_players')),
			array('permission.permission_set', array('nC Zone Mod', 'm_zone_manage_civs')),
			array('permission.permission_set', array('nC Zone Mod', 'm_zone_create_maps')),
			array('permission.permission_set', array('nC Zone Mod', 'm_zone_manage_maps')),
			array('permission.permission_set', array('nC Zone Mod', 'm_zone_login_players')),
			array('permission.permission_set', array('nC Zone Mod', 'm_zone_draw_match')),
			array('permission.permission_set', array('nC Zone Mod', 'm_zone_change_match')),
		);
	}
}
