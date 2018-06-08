<?php
/**
 *
 * nC Zone. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Marian Cepok, https://new-chapter.eu
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eru\nczone\mcp;

/**
 * nC Zone MCP module info.
 */
class main_info
{
	function module()
	{
		return array(
			'filename'	=> '\eru\nczone\mcp\main_module',
			'title'		=> 'MCP_ZONE_TITLE',
			'modes'		=> array(
				'players'	=> array(
					'title'	=> 'MCP_PLAYERS_TITLE',
					'auth'	=> 'ext_eru/nczone && acl_m_zone_manage_players',
					'cat'	=> array('MCP_ZONE_TITLE')
				),
				'civs'	=> array(
					'title'	=> 'MCP_CIVS_TITLE',
					'auth'	=> 'ext_eru/nczone && acl_m_zone_manage_civs',
					'cat'	=> array('MCP_ZONE_TITLE')
				),
				'maps'	=> array(
					'title'	=> 'MCP_MAPS_TITLE',
					'auth'	=> 'ext_eru/nczone && acl_m_zone_manage_maps',
					'cat'	=> array('MCP_ZONE_TITLE')
				),
			),
		);
	}
}
