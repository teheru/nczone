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
    /** @var driver_interface */
    private $db;
    /** @var string */
    private $matches_table;
    /** @var string */
    private $match_players_table;
    /** @var string */
    private $match_civs_table;
    /** @var string */
    private $team_civs_table;
    /** @var string */
    private $player_civs_table;
    /** @var string */
    private $maps_table;
    /** @var string */
    private $player_map_time;
    
    /**
     * Constructor
     * 
     * @param \phpbb\db\driver\driver_interface  $db                   Database object
     * @param string                             $matches_table        Name of the matches table
     * @param string                             $match_players_table  Name of the table for the teams
     * @param string                             $match_civs_table     Name of the match civs table
     * @param string                             $team_civs_table      Name of the team civs table
     * @param string                             $player_civs_table    Name of the player civs table
     * @param string                             $maps_table           Name of the maps table
     * @param string                             $player_map_time      Name of the table with the last time a player played a map
     */
    public function __construct(\phpbb\db\driver\driver_interface $db, $matches_table, $match_players_table, $match_civs_table, $team_civs_table, $player_civs_table, $maps_table, $player_map_time)
    {
        $this->db = $db;
        $this->matches_table = $matches_table;
        $this->match_players_table = $match_players_table;
        $this->match_civs_table = $match_civs_table;
        $this->team_civs_table = $team_civs_table;
        $this->player_civs_table = $player_civs_table;
        $this->maps_table = $maps_table;
        $this->player_map_times = $player_map_time;
    }


    public function get_last_team_id(): int
    {
        // TODO: this should probably done with something like sql_query_limit()
        return (int)db_utils::get_var($this->db, [
            'SELECT' => 't.team_id',
            'FROM' => [$this->match_players_table => 't'],
            'ORDER_BY' => 'team_id DESC'
        ]);
    }

    /**
     * Calculates the next map (id) to be drawn for a group of players
     * 
     * @param array  $user_ids  Ids of the users
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
