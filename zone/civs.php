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
     * @param \phpbb\db\driver\driver_interface    $db                Database object
     * @param string                               $civs_table        Name of the civs table
     * @param string                               $maps_table        Name of the maps table
     * @param string                               $map_civs_table    Name of the map civ (ratings) table
     */
    public function __construct(driver_interface $db, string $civs_table, string $map_civs_table)
    {
        $this->db = $db;
        $this->civs_table = $civs_table;
        $this->map_civs_table = $map_civs_table;
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
     * @param int    $civ_id    Id of the civ
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
     * Creates a new civ in the database
     * 
     * @param string    $civ_name    Name of the civ
     * 
     * @return void
     */
    public function create_civ($civ_name): string
    {
        $civ_id = $this->insert_civ($civ_name);
        $this->create_civ_maps($civ_id);
        return $civ_id;
    }


    private function insert_civ(string $civ_name): string
    {
        return db_util::insert($this->db, $this->civs_table, [
            'civ_id' => 0,
            'civ_name' => $civ_name
        ]);
    }

    
    private function create_civ_maps(string $civ_id): void
    {
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
}