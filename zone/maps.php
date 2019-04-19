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

use eru\nczone\config\config;
use eru\nczone\utility\db;
use eru\nczone\utility\phpbb_util;
use eru\nczone\zone\entity\map_civ;

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
     * @return entity\map[]
     */
    public function get_maps(): array
    {
        $rows = $this->db->get_rows([
            'SELECT' => 'm.map_id AS id, m.map_name AS name, m.weight, m.description,
                         m.draw_1vs1, m.draw_2vs2, m.draw_3vs3, m.draw_4vs4',
            'FROM' => [$this->db->maps_table => 'm'],
            'ORDER_BY' => 'LOWER(name) ASC',
        ]);
        return array_map([entity\map::class, 'create_by_row'], $rows);
    }

    public function get_map_ids(): array
    {
        return $this->db->get_col([
            'SELECT' => 'm.map_id',
            'FROM' => [$this->db->maps_table => 'm']
        ]);
    }

    /**
     * Returns name and weight of a certain map.
     *
     * @param int $map_id Id of the map.
     *
     * @return entity\map
     */
    public function get_map(int $map_id): entity\map
    {
        $row = $this->db->get_row([
            'SELECT' => 'm.map_id AS id, m.map_name AS name, m.weight,
                         m.draw_1vs1, m.draw_2vs2, m.draw_3vs3, m.draw_4vs4',
            'FROM' => [$this->db->maps_table => 'm'],
            'WHERE' => 'm.map_id = ' . $map_id
        ]);
        return entity\map::create_by_row($row);
    }

    /**
     * Returns the information of all civs for a certain map.
     *
     * @param int    $map_id    Id of the map.
     *
     * @return entity\map_civ[]
     */
    public function get_map_civs(int $map_id): array
    {
        $rows = $this->db->get_rows([
            'SELECT' => 'c.map_id, c.civ_id, c.multiplier, c.force_draw, c.prevent_draw, c.both_teams',
            'FROM' => [$this->db->map_civs_table => 'c'],
            'WHERE' => 'c.map_id = ' . $map_id
        ]);
        return \array_map([entity\map_civ::class, 'create_by_row'], $rows);
    }

    public function get_map_both_teams_civ_ids(int $map_id): array
    {
        return array_map('\intval', $this->db->get_col([
            'SELECT' => 'c.civ_id AS id',
            'FROM' => [$this->db->map_civs_table => 'c'],
            'WHERE' => 'c.map_id = ' . $map_id . ' AND c.both_teams',
        ]));
    }

    public function get_banned_civs(int $map_id): array
    {
        return \array_map(function($row) {
            return [
                'id' => (int)$row['id'],
                'title' => $row['title'],
           ];   
        }, $this->db->get_rows([
            'SELECT' => 'c.civ_id AS id, c.civ_name AS title',
            'FROM' => [$this->db->map_civs_table => 'mc', $this->db->civs_table => 'c'],
            'WHERE' => 'mc.civ_id = c.civ_id AND mc.map_id = ' . $map_id . ' AND mc.prevent_draw',
        ]));
    }

    public function set_map_description(int $map_id, string $description): void
    {
        $this->db->update($this->db->maps_table, ['description' => trim($description)], ['map_id' => $map_id]);
    }

    public function set_map_image(int $map_id, string $image_raw): bool
    {
        $image_raw = \base64_decode(\explode(',', $image_raw)[1]);
        $image = @\imagecreatefromstring($image_raw);
        if ($image) {
            imagesavealpha($image, TRUE);
            $width = \imagesx($image);
            $height = \imagesy($image);
            if ($width <= 200 && $height <= 500) {
                \imagepng($image, $this->get_image_path($map_id));
                return true;
            }
        }
        return false;
    }

    public function get_image_path(int $map_id): string
    {
        return phpbb_util::nczone_path() . config::map_images_path . 'map_' . $map_id . '.png';
    }

    /**
     * Creates a new map.
     *
     * @param string $map_name Name of the new map
     * @param float $weight Drawing weight
     * @param int $copy_map_id id of a map to be copied
     * @return string
     * @throws \Throwable
     */
    public function create_map(string $map_name, float $weight, array $match_sizes, int $copy_map_id = 0): string
    {
        return $this->db->run_txn(function () use ($map_name, $weight, $copy_map_id) {
            $map_id = $this->insert_map($map_name, $weight, $match_sizes);

            if ($copy_map_id) {
                $this->copy_map_civs($map_id, $copy_map_id);
            } else {
                $this->create_map_civs($map_id);
            }

            $this->insert_map_x_player_entries($map_id);
            return $map_id;
        });
    }

    private function insert_map(string $map_name, float $weight, array $match_sizes): int
    {
        return $this->db->insert($this->db->maps_table, [
            'map_id' => 0,
            'map_name' => $map_name,
            'weight' => $weight,
            'description' => '',
            'draw_1vs1' => $match_sizes[1],
            'draw_2vs2' => $match_sizes[2],
            'draw_3vs3' => $match_sizes[3],
            'draw_4vs4' => $match_sizes[4]
        ]);
    }

    private function copy_map_civs(int $to_map_id, int $from_map_id): void
    {
        $sql_array = [];
        foreach ($this->get_map_civs($from_map_id) as $map_civ) {
            $map_civ->set_map_id($to_map_id);
            $sql_array[] = $map_civ->as_sql_array();
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
            $sql_array[] = map_civ::create_by_row([
                'map_id' => $map_id,
                'civ_id' => $civ->get_id(),
            ])->as_sql_array();
        }
        if (!empty($sql_array)) {
            $this->db->sql_multi_insert($this->db->map_civs_table, $sql_array);
        }
    }

    /**
     * Edits the information (name, weight, match_sizes) of a map
     *
     * @param entity\map $map Information of the map
     *
     * @return void
     */
    public function edit_map(entity\map $map): void
    {
        $match_sizes = $map->get_match_sizes();
        $this->db->update($this->db->maps_table, [
            'map_name' => $map->get_name(),
            'weight' => $map->get_weight(),
            'draw_1vs1' => $match_sizes[1],
            'draw_2vs2' => $match_sizes[2],
            'draw_3vs3' => $match_sizes[3],
            'draw_4vs4' => $match_sizes[4]
        ], [
            'map_id' => $map->get_id(),
        ]);
    }

    /**
     * Edits the civs information for a map
     *
     * @param entity\map_civ[] $map_civs Information of the civs
     *
     * @return void
     */
    public function edit_map_civs(array $map_civs): void
    {
        foreach ($map_civs as $map_civ) {
            $this->db->update($this->db->map_civs_table, [
                'multiplier' => $map_civ->get_multiplier(),
                'force_draw' => $map_civ->get_force_draw(),
                'prevent_draw' => $map_civ->get_prevent_draw(),
                'both_teams' => $map_civ->get_both_teams()
            ], [
                'map_id' => $map_civ->get_map_id(),
                'civ_id' => $map_civ->get_civ_id(),
            ]);
        }
    }

    private function insert_map_x_player_entries(int $map_id): void
    {
        $this->db->sql_query(
            'INSERT INTO `'. $this->db->player_map_table .'` (`user_id`, `map_id`, `counter`) '.
            'SELECT user_id, "'. $map_id .'", 0 FROM `'. $this->db->players_table .'`'
        );
    }
}
