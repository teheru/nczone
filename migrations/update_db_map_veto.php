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

class update_db_map_veto extends migration
{
	public static function depends_on()
	{
		return [
			'\eru\nczone\migrations\install_db_create',
		];
	}

	public function update_schema()
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'zone_player_map' => [
					'veto' => ['BOOL', 0],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'zone_player_map' => [
					'veto',
				],
			],
		];
	}
}
