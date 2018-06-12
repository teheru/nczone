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

    /**
     * Creates a match and returns the match id
     * 
     * @param int    $draw_user_id    ID of the user who draw the game
     * @param array  $team1           Array of the players in team 1
     * @param array  $team2           Array of the players in team 2
     * @param int    $map_id          ID of the map to be played
     * @param array  $match_civ_ids   Array of civ ids for the whole match
     * @param array  $team1_civ_ids   Array of civ ids for team 1 only
     * @param array  $team2_civ_ids   Array of civ ids for team 2 only
     * @param array  $player_civ_ids  Array (user id => civ id) of civs for players
     * 
     * @return int
     */
    public function create_match(int $draw_user_id, $team1, $team2, int $map_id=0, $match_civ_ids=[], $team1_civ_ids=[], $team2_civ_ids=[], $player_civ_ids=[]): int
    {
        $team1_id = $this->get_last_team_id() + 1;
        $team2_id = $team1_id + 1;

        $team_data = [];
        foreach($team1 as $player)
        {
            $team_data[] = [
                'team_id' => $team1_id,
                'user_id' => $player['id'],
                'draw_rating' => $player['rating'],
            ];
        }
        foreach($team2 as $player)
        {
            $team_data[] = [
                'team_id' => $team2_id,
                'user_id' => $player['id'],
                'draw_rating' => $player['rating'],
            ];
        }
        db_util::multi_insert($this->db, $this->match_players_table, $team_data);

        $match_id = (int)db_util::insert($this->db, $this->matches_table, [
            'match_id' => '',
            'team1_id' => $team1_id,
            'team2_id' => $team2_id,
            'draw_user_id' => (int)$draw_user_id,
            'post_user_id' => 0,
            'draw_time' => time(),
            'post_time' => 0,
            'winner_team_id' => 0,
        ]);

        $match_civ_data = [];
        $match_civ_numbers = array_count_values($match_civ_ids);
        foreach($civ_id as array_unique($match_civ_ids))
        {
            $match_civ_data[] = [
                'match_id' => $match_id,
                'civ_id' => $civ_id,
                'number' => $match_civ_numbers[$civ_id],
            ];
        }
        db_util::multi_insert($this->db, $this->match_civs_table, $match_civ_data);

        
        $team_civ_data = [];
        $team1_civ_numbers = array_count_values($team1_civ_numbers);
        foreach($civ_id as array_unique($team1_civ_ids))
        {
            $team_civ_data[] = [
                'team_id' => $team1_id,
                'civ_id' => (int)$civ_id,
                'number' => $team1_civ_numbers[$civ_id],
            ];
        }
        $team2_civ_numbers = array_count_values($team2_civ_numbers);
        foreach($civ_id as array_unique($team2_civ_ids))
        {
            $team_civ_data[] = [
                'team_id' => $team2_id,
                'civ_id' => (int)$civ_id,
                'number' => $team2_civ_numbers[$civ_id],
            ];
        }
        db_util::multi_insert($this->db, $this->team_civs_table, $team_civ_data);


        $player_civ_data = [];
        foreach($player_civ_ids as $user_id => $civ_id)
        {
            $player_civ_data[] = [
                'match_id' => $match_id,
                'user_id' = (int)$user_id,
                'civ_id' = (int)$civ_id,
            ];
        }
        db_util::multi_insert($this->db, $this->player_civs_table, $player_civ_data);

        return $match_id;
    }

    public function get_last_team_id(): int
    {
        // TODO: this should probably done with something like sql_query_limit()
        return (int)db_util::get_var($this->db, [
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
