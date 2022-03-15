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

class update_db_map_variants extends migration
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
            'add_tables' => [
                $this->table_prefix . 'zone_map_variants' => [
                    'COLUMNS' => [
                        'map_id' => ['UINT', 0],
                        'variant_map_id' => ['UINT', 0]
                    ],
                    'PRIMARY_KEY' => ['map_id', 'variant_map_id'],
                ]
			]
		];
	}

	public function revert_schema()
	{
		return [
            'drop_tables' => [
                $this->table_prefix . 'zone_map_variants',
			]
		];
	}
}
