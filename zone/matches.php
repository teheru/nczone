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

/**
 * nC Zone matches management class.
 */
class matches {
    // TODO: attributes
    
    /**
     * Constructor
     * 
     * @param \phpbb\db\driver\driver_interface    $db                 Database object
     * 
     * @param string                               $maps_table         Name of the maps table
     * @param string                               $player_map_time    Name of the table with the last time a player played a map.
     */
    public function __construct(\phpbb\db\driver\driver_interface $db, $maps_table, $player_map_time)
    {
        $this->db = $db;
        $this->maps_table = $maps_table;
        $this->player_map_times = $player_map_time;
    }

    /**
     * Calculates the next map (id) to be drawn for a group of players
     * 
     * @param array    $user_ids    Ids of the users
     * 
     * @return int
     */
    public function get_players_map_id($user_ids)
    {
        return db_util::get_var($this->db, [
            'SELECT' => 't.map_id',
            'FROM' => [$this->player_map_times => 't', $this->maps_table => 'm'],
            'WHERE' => 't.map_id = m.map_id AND ' . $this->db->sql_in_set('t.user_id', $user_ids),
            'GROUP_BY' => 't.map_id',
            'ORDER_BY' => 'LOG(60, SUM(UNIX_TIMESTAMP() - t.time)) * m.weight DESC'
        ]);
    }
}
