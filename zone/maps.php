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
use phpbb\db\driver\driver_interface;

class maps
{
    /** @var driver_interface */
    private $db;
    /** @var civs */
    private $zone_civs;
    /** @var string */
    private $maps_table;
    /** @var string */
    private $map_civs_table;

    public function __construct(driver_interface $db, civs $zone_civs, string $maps_table, string $map_civs_table)
    {
        $this->db = $db;
        $this->zone_civs = $zone_civs;
        $this->maps_table = $maps_table;
        $this->map_civs_table = $map_civs_table;
    }

    public function get_maps(): array
    {
        return db_util::get_rows($this->db, [
            'SELECT' => 'm.map_id AS id, m.map_name AS name, m.weight AS weight',
            'FROM' => [$this->maps_table => 'm']
        ]);
    }

    public function get_map_ids(): array
    {
        return db_util::get_col($this->db, [
            'SELECT' => 'm.map_id AS map_id',
            'FROM' => [$this->maps_table => 'm']
        ]);
    }

    public function get_map($map_id)
    {
        return db_util::get_row($this->db, [
            'SELECT' => 'm.map_name AS name, m.weight AS weight',
            'FROM' => [$this->maps_table => 'm'],
            'WHERE' => 'm.map_id = ' . (int)$map_id
        ]);
    }

    public function get_map_civs($map_id): array
    {
        return db_util::get_rows($this->db, [
            'SELECT' => 'c.civ_id AS civ_id, c.multiplier AS multiplier, c.force_draw AS force_draw, c.prevent_draw AS prevent_draw, c.both_teams AS both_teams',
            'FROM' => [$this->map_civs_table => 'c'],
            'WHERE' => 'c.map_id = ' . (int)$map_id
        ]);
    }

    public function create_map(string $map_name, float $weight, int $copy_map_id = 0): string
    {
        $map_id = $this->insert_map($map_name, $weight);

        if ($copy_map_id) {
            $this->copy_map_civs($map_id, $copy_map_id);
        } else {
            $this->create_map_civs($map_id);
        }

        return $map_id;
    }

    private function insert_map(string $map_name, float $weight): string
    {
        return db_util::insert($this->db, $this->maps_table, [
            'map_id' => 0,
            'map_name' => $map_name,
            'weight' => $weight
        ]);
    }

    private function copy_map_civs($to_map_id, $from_map_id): void
    {
        $sql_array = [];
        foreach ($this->get_map_civs($from_map_id) as $map_civ) {
            $map_civ['map_id'] = $to_map_id;
            $sql_array[] = $map_civ;
        }
        if (!empty($sql_array)) {
            $this->db->sql_multi_insert($this->map_civs_table, $sql_array);
        }
    }

    private function create_map_civs($map_id): void
    {
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
            $this->db->sql_multi_insert($this->map_civs_table, $sql_array);
        }
    }

    public function edit_map(int $map_id, array $map_info): void
    {
        $sql_array = [];
        if (array_key_exists('name', $map_info)) {
            $sql_array['map_name'] = $map_info['name'];
        }
        if (array_key_exists('weight', $map_info)) {
            $sql_array['weight'] = (float)$map_info['weight'];
        }

        db_util::update($this->db, $this->maps_table, $sql_array, [
            'map_id' => $map_id,
        ]);
    }

    public function edit_map_civs(int $map_id, array $map_civs): void
    {
        foreach ($map_civs as $civ_id => $map_civ) {
            db_util::update($this->db, $this->map_civs_table, [
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
