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

use eru\nczone\utility\db;
use eru\nczone\utility\number_util;
use eru\nczone\utility\zone_util;

class civs
{
    /** @var db */
    private $db;

    /**
     * Constructor
     *
     * @param db $db Database object
     */
    public function __construct(db $db)
    {
        $this->db = $db;
    }

    public static function sort_by_multiplier(array $civs): array
    {
        \usort($civs, function ($c1, $c2) {
            return number_util::cmp($c1['multiplier'], $c2['multiplier']);
        });
        return $civs;
    }

    /**
     * Returns the id and name of all civs
     *
     * @return entity\civ[]
     */
    public function get_civs(): array
    {
        $rows = $this->db->get_rows([
            'SELECT' => 'c.civ_id AS id, c.civ_name AS name',
            'FROM' => [$this->db->civs_table => 'c']
        ]);
        return \array_map([entity\civ::class, 'create_by_row'], $rows);
    }


    /**
     * Returns the name of a certain civ
     *
     * @param int $civ_id Id of the civ
     *
     * @return entity\civ
     */
    public function get_civ(int $civ_id): entity\civ
    {
        $row = $this->db->get_row([
            'SELECT' => 'c.civ_id AS id, c.civ_name AS name',
            'FROM' => [$this->db->civs_table => 'c'],
            'WHERE' => 'c.civ_id = ' . $civ_id
        ]);
        return entity\civ::create_by_row($row);
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
        $this->db->update(
            $this->db->civs_table,
            ['civ_name' => $civ_info['name']],
            ['civ_id' => $civ_id]
        );
    }


    /**
     * Creates a new civ and civ map entries in the database, returns the id of the new civ
     *
     * @param string $civ_name Name of the civ
     *
     * @return int
     * @throws \Throwable
     */
    public function create_civ(string $civ_name): int
    {
        return $this->db->run_txn(function() use ($civ_name) {
            $civ_id = $this->insert_civ($civ_name);
            $this->insert_civ_x_map_entries($civ_id);
            $this->insert_civ_x_player_entries($civ_id);
            return $civ_id;
        });
    }


    /**
     * Creates a new civ entry in the database without entries for related tables, returns the id of the new civ
     *
     * @param string $civ_name Name of the civ
     *
     * @return int
     */
    private function insert_civ(string $civ_name): int
    {
        return $this->db->insert($this->db->civs_table, [
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
    private function insert_civ_x_map_entries(int $civ_id): void
    {
        // todo: replace this by a subquery like above?
        foreach (zone_util::maps()->get_map_ids() as $map_id) {
            $this->db->insert($this->db->map_civs_table, [
                'map_id' => (int)$map_id,
                'civ_id' => $civ_id,
                'multiplier' => 0.0,
                'force_draw' => false,
                'prevent_draw' => false,
                'both_teams' => false,
            ]);
        }
    }

    private function insert_civ_x_player_entries(int $civ_id): void
    {
        $this->db->sql_query(
            'INSERT INTO `'. $this->db->player_civ_table .'` (`user_id`, `civ_id`, `time`) '.
            'SELECT user_id, "'. $civ_id .'", "'. time() .'" FROM `'. $this->db->players_table .'`'
        );
    }

    public function get_map_players_multiple_civs(
        int $map_id,
        array $user_ids,
        array $civ_ids = []
    ): array {
        return $this->db->get_rows($this->get_map_players_civs_build_sql_array(
            $map_id,
            $user_ids,
            $civ_ids,
            true,
            false
        ));
    }

    public function get_map_players_single_civ(
        int $map_id,
        array $user_ids,
        array $civ_ids = [],
        bool $neg_civ_ids = false,
        bool $force = false
    ) {
        return $this->db->get_row($this->get_map_players_civs_build_sql_array(
            $map_id,
            $user_ids,
            $civ_ids,
            $neg_civ_ids,
            $force
        ));
    }

    private function get_map_players_civs_build_sql_array(
        int $map_id,
        array $user_ids,
        array $civ_ids = [],
        bool $neg_civ_ids = false,
        bool $force = false
    ): array {
        $sql_array = [
            'SELECT' => 'p.user_id AS user_id, c.civ_id AS civ_id, c.multiplier AS multiplier, c.both_teams AS both_teams',
            'FROM' => array($this->db->map_civs_table => 'c', $this->db->player_civ_table => 'p'),
            'WHERE' => 'c.map_id = ' . (int)$map_id . ' AND c.civ_id = p.civ_id AND NOT c.prevent_draw AND ' . $this->db->sql_in_set('p.user_id', $user_ids),
            'GROUP_BY' => 'p.user_id, c.civ_id',
            'ORDER_BY' => 'SUM(' . \time() . ' - p.time) DESC',
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
