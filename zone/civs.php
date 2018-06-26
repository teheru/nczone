<?php

/**
 *
 * nC Zone. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Marian Cepok, https://new-chapter.eu
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eru\nczone\zone;

use eru\nczone\utility\db_util;
use eru\nczone\utility\number_util;
use eru\nczone\utility\zone_util;
use phpbb\db\driver\driver_interface;

class civs
{
    /** @var driver_interface */
    private $db;
    /** @var string */
    private $civs_table;
    /** @var string */
    private $map_civs_table;


    /**
     * Constructor
     *
     * @param \phpbb\db\driver\driver_interface  $db                Database object
     * @param string                             $civs_table        Name of the civs table
     * @param string                             $maps_table        Name of the maps table
     * @param string                             $map_civs_table    Name of the map civ (ratings) table
     * @param string                             $player_civ_table  Name of the player civ (time) table
     */
    public function __construct(driver_interface $db, string $civs_table, string $players_table, string $maps_table, string $map_civs_table, string $player_civ_table)
    {
        $this->db = $db;
        $this->civs_table = $civs_table;
        $this->players_table = $players_table;
        $this->maps_table = $maps_table;
        $this->map_civs_table = $map_civs_table;
        $this->player_civ_table = $player_civ_table;
    }

    public static function sort_by_multiplier(array $civs): array
    {
        usort($civs, function ($c1, $c2) {
            return number_util::cmp($c1['multiplier'], $c2['multiplier']);
        });
        return $civs;
    }

    /**
     * Returns the id and name of all civs
     *
     * @return array
     */
    public function get_civs(): array
    {
        return db_util::get_rows($this->db, [
            'SELECT' => 'c.civ_id AS id, c.civ_name AS name',
            'FROM' => [$this->civs_table => 'c']
        ]);
    }


    /**
     * Returns the name of a certain civ
     *
     * @param int  $civ_id  Id of the civ
     *
     * @return string
     */
    public function get_civ(int $civ_id)
    {
        return db_util::get_row($this->db, [
            'SELECT' => 'c.civ_name AS name',
            'FROM' => [$this->civs_table => 'c'],
            'WHERE' => 'c.civ_id = ' . $civ_id
        ]);
    }


    /**
     * Edits the info of a civ
     *
     * @param int    $civ_id    Id of the civ
     * @param array  $civ_info  Information of the civ (i.e. name)
     *
     * @return void
     */
    public function edit_civ(int $civ_id, array $civ_info): void
    {
        db_util::update($this->db, $this->civs_table, ['civ_name' => $civ_info['name']], ['civ_id' => $civ_id]);
    }


    /**
     * Creates a new civ and civ map entries in the database, returns the id of the new civ
     *
     * @param string  $civ_name  Name of the civ
     *
     * @return string
     */
    public function create_civ($civ_name): string
    {
        $this->db->sql_transaction('begin');

        $civ_id = $this->insert_civ($civ_name);
        $this->create_civ_maps($civ_id);
        $this->db->sql_query('INSERT INTO `'. $this->player_civ_table .'` (`user_id`, `civ_id`, `time`) SELECT user_id, "'. $civ_id .'", "'. time() .'" FROM `'. $this->players_table .'`');

        $this->db->sql_transaction('commit');

        return $civ_id;
    }


    /**
     * Creates a new civ entry in the database without entries for related tables, returns the id of the new civ
     *
     * @param string  $civ_name  Name of the civ
     *
     * @return string
     */
    private function insert_civ(string $civ_name): string
    {
        return db_util::insert($this->db, $this->civs_table, [
            'civ_id' => 0,
            'civ_name' => $civ_name
        ]);
    }


    /**
     * Create entries for the civ x map table.
     *
     * @param int  $civ_id  The id of the civ
     *
     * @return void
     */
    private function create_civ_maps(int $civ_id): void // todo: here was string $civ_id before.... string??
    {
        // todo: replace this by a subquery like above?
        foreach (zone_util::maps()->get_map_ids() as $map_id) {
            db_util::insert($this->db, $this->map_civs_table, [
                'map_id' => $map_id,
                'civ_id' => $civ_id,
                'multiplier' => 0.0,
                'force_draw' => false,
                'prevent_draw' => false,
                'both_teams' => false,
            ]);
        }
    }

    public function get_map_players_multiple_civs($map_id, $user_ids, array $civ_ids = [], $neg_civ_ids = False, $force = False): array
    {
        $sql_array = $this->get_map_players_civs_build_sql_array($map_id, $user_ids, $civ_ids, $neg_civ_ids, $force);
        return db_util::get_rows($this->db, $sql_array);
    }

    public function get_map_players_single_civ($map_id, $user_ids, array $civ_ids = [], $neg_civ_ids = False, $force = False)
    {
        $sql_array = $this->get_map_players_civs_build_sql_array($map_id, $user_ids, $civ_ids, $neg_civ_ids, $force);
        return db_util::get_row($this->db, $sql_array);
    }

    private function get_map_players_civs_build_sql_array($map_id, $user_ids, array $civ_ids = [], $neg_civ_ids = False, $force = False): array
    {
        $sql_array = [
            'SELECT' => 'p.user_id AS user_id, c.civ_id AS civ_id, c.multiplier AS multiplier, c.both_teams AS both_teams',
            'FROM' => array($this->map_civs_table => 'c', $this->player_civ_table => 'p'),
            'WHERE' => 'c.map_id = ' . (int)$map_id . ' AND c.civ_id = p.civ_id AND NOT c.prevent_draw AND ' . $this->db->sql_in_set('p.user_id', $user_ids),
            'GROUP_BY' => 'p.user_id, c.civ_id',
            'ORDER_BY' => 'SUM(' . time() . ' - p.time) DESC',
        ];

        if (\count($civ_ids) > 0) {
            $sql_array['WHERE'] .= ' AND ' . $this->db->sql_in_set('c.civ_id', $civ_ids, $neg_civ_ids);
        }
        if ($force) {
            $sql_array['WHERE'] .= ' AND c.force_draw';
        }

        return $sql_array;
    }
}
