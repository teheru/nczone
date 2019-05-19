<?php

use eru\nczone\utility\db;

class db_test extends \phpbb_database_test_case
{
    /**
     * @dataProvider build_query_string_data_provider
     * @param $expected
     * @param $type
     * @param $data
     */
    public function test_build_query_string($type, $data, $expected): void
    {
        global $table_prefix;
        $db = new db($this->new_dbal(), $table_prefix);
        self::assertSame($expected, $db->build_query_string($type, $data));
    }

    public function build_query_string_data_provider(): array
    {
        return [
            [
                'SELECT',
                [
                    'SELECT' => 'c.civ_id AS id, c.civ_name AS name',
                    'FROM' => ['phpbb_civs_table' => 'c'],
                    'WHERE' => 'c.civ_id = 1',
                ],
                'SELECT c.civ_id AS id, c.civ_name AS name FROM phpbb_civs_table c WHERE c.civ_id = 1'
            ],
            [
                'SELECT',
                [
                    'SELECT' => 'c.civ_id AS id, c.civ_name AS name',
                    'FROM' => ['phpbb_civs_table' => 'c'],
                    'WHERE' => ['c.civ_id' => 1],
                ],
                'SELECT c.civ_id AS id, c.civ_name AS name FROM phpbb_civs_table c WHERE `c`.`civ_id` = 1'
            ],
            [
                'SELECT',
                [
                    'SELECT' => 'c.civ_id AS id, c.civ_name AS name',
                    'FROM' => ['phpbb_civs_table' => 'c'],
                    'WHERE' => ['c.civ_id' => ['$ne' => 1]],
                ],
                'SELECT c.civ_id AS id, c.civ_name AS name FROM phpbb_civs_table c WHERE `c`.`civ_id` != 1'
            ],
            [
                'SELECT',
                [
                    'SELECT' => 'c.civ_id AS id, c.civ_name AS name',
                    'FROM' => ['phpbb_civs_table' => 'c'],
                    'WHERE' => ['c.civ_id' => ['$ne' => null]],
                ],
                'SELECT c.civ_id AS id, c.civ_name AS name FROM phpbb_civs_table c WHERE `c`.`civ_id` IS NOT NULL'
            ],
            [
                'SELECT',
                [
                    'SELECT' => 'b.user_id, b.team_id, b.time, u.username',
                    'FROM' => ['phpbb_bets_table' => 'b', 'phpbb_users_table' => 'u'],
                    'WHERE' => [
                        'b.user_id = u.user_id',
                        'b.team_id' =>  [4, 5],
                    ],
                ],
                "SELECT b.user_id, b.team_id, b.time, u.username FROM phpbb_bets_table b CROSS JOIN phpbb_users_table u WHERE b.user_id = u.user_id AND `b`.`team_id` IN (4,5)"
            ],
            [
                'SELECT',
                [
                    'SELECT' => 'c.civ_id AS id, c.civ_name AS name',
                    'FROM' => ['phpbb_civs_table' => 'c'],
                    'WHERE' => ['c.civ_id' => ['$in' => [5, 1, 7, 'alpha']]],
                ],
                "SELECT c.civ_id AS id, c.civ_name AS name FROM phpbb_civs_table c WHERE `c`.`civ_id` IN (5,1,7,'alpha')"
            ],
            [
                'SELECT',
                [
                    'FROM' => ['phpbb_civs_table' => 'c'],
                    'WHERE' => ['c.civ_name' => 'Los\'Berberos'],
                ],
                "SELECT * FROM phpbb_civs_table c WHERE `c`.`civ_name` = 'Los''Berberos'"
            ],
            [
                'SELECT',
                [
                    'FROM' => ['phpbb_civs_table' => 'x'],
                    'WHERE' => ['civ_id' => ['$lte' => 5]],
                ],
                'SELECT * FROM phpbb_civs_table x WHERE `civ_id` <= 5'
            ],
            [
                'SELECT',
                [
                    'FROM' => ['phpbb_civs_table' => 'x'],
                    'WHERE' => ['civ_id' => [1, 5]],
                ],
                'SELECT * FROM phpbb_civs_table x WHERE `civ_id` IN (1,5)'
            ],
        ];
    }

    /**
     * Returns the test dataset.
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return new PHPUnit_Extensions_Database_DataSet_ArrayDataSet([]);
    }
}
