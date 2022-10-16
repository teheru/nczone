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

use phpbb\db\migration\migration;

class update_db_player_civs extends migration
{
	public static function depends_on()
	{
		return [
			'\eru\nczone\migrations\install_db_create',
		];
	}

	public function update_schema()
	{
		$table_name = $this->table_prefix . 'zone_match_player_civs';

		$sql = 'SELECT * FROM ' . $table_name;
		$this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset();
		$this->db->sql_freeresult();

		$this->db_tools->sql_table_drop($table_name);
		$this->db_tools->sql_create_table($table_name, [
			'COLUMNS' => [
				'match_id' => ['UINT', 0],
				'user_id' => ['UINT', 0],
				'civ_id' => ['UINT', 0],
			],
			'PRIMARY_KEY' => ['match_id', 'user_id', 'civ_id']
		]);

		$this->db->sql_multi_insert($table_name, $rows);
	}
}
