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

/**
 * nC Zone maps management class.
 */
class maps
{
    /** @var db */
    private $db;
    /** @var civs */
    private $zone_civs;

    /**
     * Constructor.
     *
     * @param db $db Database object
     * @param civs $zone_civs Civs object
     */
    public function __construct(db $db, civs $zone_civs)
    {
        $this->db = $db;
        $this->zone_civs = $zone_civs;
    }

    /**
     * Returns all maps (id, name, weight)
     *
     * @return array
     */
    public function get_maps(): array
    {
        return $this->db->get_rows([
            'SELECT' => 'm.map_id AS id, m.map_name AS name, m.weight AS weight',
            'FROM' => [$this->db->maps_table => 'm']
        ]);
    }


    public function get_map_ids(): array
    {
        return $this->db->get_col([
            'SELECT' => 'm.map_id AS map_id',
            'FROM' => [$this->db->maps_table => 'm']
        ]);
    }

    /**
     * Returns name and weight of a certain map.
     *
     * @param int    $map_id    Id of the map.
     *
     * @return array
     */
    public function get_map(int $map_id): array
    {
        return $this->db->get_row([
            'SELECT' => 'm.map_name AS name, m.weight AS weight',
            'FROM' => [$this->db->maps_table => 'm'],
            'WHERE' => 'm.map_id = ' . $map_id
        ]);
    }

    /**
     * Returns the information of all civs for a certain map.
     *
     * @param int    $map_id    Id of the map.
     *
     * @return array
     */
    public function get_map_civs(int $map_id): array
    {
        return $this->db->get_rows([
            'SELECT' => 'c.civ_id AS civ_id, c.multiplier AS multiplier, c.force_draw AS force_draw, c.prevent_draw AS prevent_draw, c.both_teams AS both_teams',
            'FROM' => [$this->db->map_civs_table => 'c'],
            'WHERE' => 'c.map_id = ' . $map_id
        ]);
    }

    public function get_map_both_teams_civ_ids(int $map_id): array
    {
        $rows = $this->db->get_rows([
            'SELECT' => 'c.civ_id AS id',
            'FROM' => [$this->db->map_civs_table => 'c'],
            'WHERE' => 'c.map_id = ' . $map_id . ' AND c.both_teams',
        ]);
        $ids = [];
        foreach($rows as $row)
        {
            $ids[] = (int)$row['id'];
        }
        return $ids;
    }

    /**
     * Creates a new map.
     *
     * @param string $map_name Name of the new map
     * @param float $weight Drawing weight
     * @param int $copy_map_id id of a map to be copied
     * @throws \Throwable
     */
    public function create_map(string $map_name, float $weight, int $copy_map_id = 0): string
    {
        return $this->db->run_txn(function () use ($map_name, $weight, $copy_map_id) {
            $map_id = $this->insert_map($map_name, $weight);

            if ($copy_map_id) {
                $this->copy_map_civs($map_id, $copy_map_id);
            } else {
                $this->create_map_civs($map_id);
            }

            $this->db->sql_query(
                'INSERT INTO `'. $this->db->player_map_table .'` (`user_id`, `map_id`, `time`) '.
                'SELECT user_id, "'. $map_id .'", "'. time() .'" FROM `'. $this->db->players_table .'`'
            );
            return $map_id;
        });
    }


    private function insert_map(string $map_name, float $weight): string
    {
        return $this->db->insert($this->db->maps_table, [
            'map_id' => 0,
            'map_name' => $map_name,
            'weight' => $weight
        ]);
    }


    private function copy_map_civs(int $to_map_id, int $from_map_id): void
    {
        $sql_array = [];
        foreach ($this->get_map_civs($from_map_id) as $map_civ) {
            $map_civ['map_id'] = $to_map_id;
            $sql_array[] = $map_civ;
        }
        if (!empty($sql_array)) {
            $this->db->sql_multi_insert($this->db->map_civs_table, $sql_array);
        }
    }


    private function create_map_civs(int $map_id): void
    {
        // todo: replace this by a subquery like above?
        $sql_array = [];
        foreach ($this->zone_civs->get_civs() as $civ) {
            $sql_array[] = [
                'map_id' => $map_id,
                'civ_id' => (int)$civ['id'],
                'multiplier' => 0.0,
                'force_draw' => false,
                'prevent_draw' => false,
                'both_teams' => false
            ];
        }
        if (!empty($sql_array)) {
            $this->db->sql_multi_insert($this->db->map_civs_table, $sql_array);
        }
    }

    /**
     * Edits the information (name, weight) of a map
     *
     * @param int      $map_id      Id of the map
     * @param array    $map_info    Information of the map
     *
     * @return void
     */
    public function edit_map(int $map_id, array $map_info): void
    {
        $sql_array = [];
        if (array_key_exists('name', $map_info)) {
            $sql_array['map_name'] = $map_info['name'];
        }
        if (array_key_exists('weight', $map_info)) {
            $sql_array['weight'] = (float)$map_info['weight'];
        }

        $this->db->update($this->db->maps_table, $sql_array, [
            'map_id' => $map_id,
        ]);
    }

    /**
     * Edits the civs information for a map
     *
     * @param int      $map_id      Id of the map
     * @param array    $map_civs    Information of the civs
     *
     * @return void
     */
    public function edit_map_civs(int $map_id, array $map_civs): void
    {
        foreach ($map_civs as $civ_id => $map_civ) {
            $this->db->update($this->db->map_civs_table, [
                'multiplier' => (float)$map_civ['multiplier'],
                'force_draw' => (bool)$map_civ['force_draw'],
                'prevent_draw' => (bool)$map_civ['prevent_draw'],
                'both_teams' => (bool)$map_civ['both_teams']
            ], [
                'map_id' => $map_id,
                'civ_id' => (int)$civ_id,
            ]);
        }
    }
}
