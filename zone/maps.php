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
     * @param actvity_only Gives only maps with weight > 0
     * 
     * @return entity\map[]
     */
    public function get_maps(bool $active_only): array
    {
        $rows = $this->db->get_rows([
            'SELECT' => [
                'map_id',
                'map_name',
                'weight',
                'description',
                'draw_1vs1',
                'draw_2vs2',
                'draw_3vs3',
                'draw_4vs4',
            ],
            'FROM' => [$this->db->maps_table => 't'],
            'WHERE' => $active_only ? 'weight > 0' : '',
            'ORDER_BY' => 'LOWER(map_name) ASC',
        ]);
        return entity\map::create_many_by_rows($rows);
    }

    public function get_map_ids(): array
    {
        return $this->db->get_col([
            'SELECT' => 'map_id',
            'FROM' => [$this->db->maps_table => 't']
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
            'SELECT' => [
                'map_id',
                'map_name',
                'weight',
                'draw_1vs1',
                'draw_2vs2',
                'draw_3vs3',
                'draw_4vs4'
            ],
            'FROM' => [$this->db->maps_table => 't'],
            'WHERE' => ['map_id' => $map_id],
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
            'SELECT' => [
                'map_id',
                'civ_id',
                'multiplier',
                'force_draw',
                'prevent_draw',
                'both_teams',
            ],
            'FROM' => [$this->db->map_civs_table => 't'],
            'WHERE' => ['map_id' => $map_id]
        ]);
        return \array_map([entity\map_civ::class, 'create_by_row'], $rows);
    }

    public function get_map_both_teams_civ_ids(int $map_id): array
    {
        return $this->db->get_int_col([
            'SELECT' => 'civ_id',
            'FROM' => [$this->db->map_civs_table => 't'],
            'WHERE' => ['map_id' => $map_id, 'both_teams'],
        ]);
    }

    public function get_banned_civs(int $map_id): array
    {
        return \array_map(static function($row) {
            return [
                'id' => (int)$row['id'],
                'title' => $row['title'],
           ];
        }, $this->db->get_rows([
            'SELECT' => ['c.civ_id' => 'id', 'c.civ_name' => 'title'],
            'FROM' => [$this->db->map_civs_table => 'mc', $this->db->civs_table => 'c'],
            'WHERE' => 'mc.civ_id = c.civ_id AND mc.map_id = ' . $map_id . ' AND mc.prevent_draw',
        ]));
    }

    public function set_map_description(int $map_id, string $description): void
    {
        $this->db->update(
            $this->db->maps_table,
            ['description' => \trim($description)],
            ['map_id' => $map_id]
        );
    }

    public function set_map_image(int $map_id, string $image_raw): bool
    {
        $image_decoded = \base64_decode(\explode(',', $image_raw)[1]);
        $image = @\imagecreatefromstring($image_decoded);
        if (!$image) {
            return false;
        }

        \imagesavealpha($image, TRUE);
        if (\imagesx($image) > 200 || \imagesy($image) > 500) {
            return false;
        }

        return \imagepng($image, $this->get_image_path($map_id));
    }

    private function get_image_path(int $map_id): string
    {
        return phpbb_util::nczone_path()
            . config::map_images_path
            . "map_{$map_id}.png";
    }

    public function get_image_url(int $map_id): string
    {
        $map_image_path = $this->get_image_path($map_id);
        return \file_exists($map_image_path)
            ? generate_board_url() . \substr($map_image_path, 1)
            : '';
    }

    public function get_weighted_veto(): array
    {
        $sql = <<<SQL
SELECT
    pm.map_id,
    AVG(pm.veto) AS weighted_veto
FROM phpbb_zone_player_map pm
LEFT JOIN phpbb_zone_players p ON pm.user_id = p.user_id
LEFT JOIN phpbb_zone_maps m ON pm.map_id = m.map_id
WHERE
    m.weight > 0
    AND p.activity_matches >= (SELECT config_value FROM phpbb_config WHERE config_name = 'nczone_activity_1')
GROUP BY pm.map_id
SQL;

        return \array_map(function ($row) {
            return [
                'map_id' => (int)$row['map_id'],
                'weighted_veto' => (float)$row['weighted_veto'],
            ];
        }, $this->db->get_rows($sql));
    }

    /**
     * Creates a new map.
     *
     * @param string $map_name Name of the new map
     * @param float $weight Drawing weight
     * @param array $match_sizes
     * @param int $copy_map_id id of a map to be copied
     * @return string
     * @throws \Throwable
     */
    public function create_map(string $map_name, float $weight, array $match_sizes, int $copy_map_id = 0): string
    {
        return $this->db->run_txn(function () use ($map_name, $weight, $match_sizes, $copy_map_id) {
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
        if ($sql_array) {
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
        if ($sql_array) {
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
            'INSERT INTO `'. $this->db->player_map_table .'` (`user_id`, `map_id`, `counter`, `veto`) '.
            'SELECT user_id, "'. $map_id .'", 0, 0 FROM `'. $this->db->players_table .'`'
        );
    }

    public function remove_all_vetos_for_map_id(int $map_id): void
    {
        $this->db->sql_query('UPDATE ' . $this->db->player_map_table . ' SET veto = 0 WHERE map_id = ' . $map_id);
    }
}
