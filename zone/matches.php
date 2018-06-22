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
use eru\nczone\utility\phpbb_util;
use phpbb\db\driver\driver_interface;

/**
 * nC Zone matches management class.
 */
class matches {

    /** @var driver_interface */
    private $db;
    /** @var string */
    private $matches_table;
    /** @var string */
    private $match_teams_table;
    /** @var string */
    private $match_players_table;
    /** @var string */
    private $match_civs_table;
    /** @var string */
    private $team_civs_table;
    /** @var string */
    private $match_player_civs_table;
    /** @var string */
    private $maps_table;
    /** @var string */
    private $civs_table;
    /** @var string */
    private $player_map_table;
    /** @var string */
    private $map_civs_table;
    /** @var string */
    private $player_civ_table;
    /** @var string */
    private $bets_table;
    /** @var string */
    private $draw_process_table;
    /** @var string */
    private $draw_players_table;
    /** @var string */
    private $users_table;

    /**
     * Constructor
     *
     * @param driver_interface  $db                       Database object
     * @param string                             $matches_table            Name of the matches table
     * @param string                             $match_teams_table        Name of the table for the players of the teams
     * @param string                             $match_players_table      Name of the table for the teams
     * @param string                             $match_civs_table         Name of the match civs table
     * @param string                             $team_civs_table          Name of the team civs table
     * @param string                             $match_player_civs_table  Name of the player civs table
     * @param string                             $maps_table               Name of the maps table
     * @param string                             $civs_table               Name of the civs table
     * @param string                             $player_map_table         Name of the table with the last time a player played a map
     * @param string                             $map_civs_table           Name of the table which contains civ information for maps
     * @param string                             $player_civ_table         Name of the table which contains the last time a player played a civ
     * @param string                             $bets_table               Name of the bets table
     * @param string                             $draw_process_table       
     * @param string                             $draw_players_table       
     * @param string                             $players_table               
     * @param string                             $users_table               
     */
    public function __construct(driver_interface $db, string $matches_table, string $match_teams_table,
                                string $match_players_table, string $match_civs_table, string $team_civs_table,
                                string $match_player_civs_table, string $maps_table, string $civs_table, string $player_map_table,
                                string $map_civs_table, string $player_civ_table, string $draw_process_table, string $draw_players_table,
                                string $bets_table, string $players_table, string $users_table)
    {
        $this->db = $db;
        $this->matches_table = $matches_table;
        $this->match_teams_table = $match_teams_table;
        $this->match_players_table = $match_players_table;
        $this->match_civs_table = $match_civs_table;
        $this->match_player_civs_table = $match_player_civs_table;
        $this->maps_table = $maps_table;
        $this->civs_table = $civs_table;
        $this->map_civs_table = $map_civs_table;
        $this->team_civs_table = $team_civs_table;
        $this->player_map_table = $player_map_table;
        $this->player_civ_table = $player_civ_table;
        $this->draw_process_table = $draw_process_table;
        $this->draw_players_table = $draw_players_table;
        $this->bets_table = $bets_table;
        $this->players_table = $players_table;
        $this->users_table = $users_table;
    }


    public function draw(int $draw_user_id, array $draw_players): array
    {
        $match_ids = [];
        $user_ids = [];

        if(\count($draw_players) < 2) {
            return [];
        }

        $matches = zone_util::draw_teams()->make_matches($draw_players);
        foreach ($matches as $match) {
            [$map_id, $match_civ_ids, $team1_civ_ids, $team2_civ_ids, $player_civ_ids]
                = zone_util::draw_settings()->draw_settings($match[0], $match[1]);
            $match_ids[] = $this->create_match($draw_user_id, $match[0], $match[1], $map_id, $match_civ_ids,
                $team1_civ_ids, $team2_civ_ids, $player_civ_ids);

            foreach (array_merge($match[0], $match[1]) as $player) {
                $user_ids[] = (int)$player['id'];
            }
        }

        zone_util::players()->logout_players($user_ids);

        return $match_ids;
    }

    public function replace_player(int $replace_user_id, int $match_id, int $player1_id, int $player2_id): int
    {
        $match_players = $this->get_teams_players(...$this->get_match_team_ids($match_id));

        if(array_key_exists($player1_id, $match_players) && !array_key_exists($player2_id, $match_players))
        {
            $this->clean_match($match_id);

            unset($match_players[$player1_id]);
            // todo: this contains much more information than needed, but is it much overhead?
            $match_players[$player2_id] = zone_util::players()->get_player($player2_id);

            [$match] = zone_util::draw_teams()->make_matches($match_players);
            [$map_id, $match_civ_ids, $team1_civ_ids, $team2_civ_ids, $player_civ_ids] = zone_util::draw_settings()->draw_settings($match[0], $match[1]);
            return $this->create_match($replace_user_id, $match[0], $match[1], $map_id, $match_civ_ids, $team1_civ_ids, $team2_civ_ids, $player_civ_ids);
        }
        else
        {
            return 0;
        }
    }

    public function add_pair(int $add_user_id, int $match_id, int $player1_id, int $player2_id): int
    {
        $match_players = $this->get_teams_players(...$this->get_match_team_ids($match_id));

        if(\count($match_players) < 8)
        {
            $this->clean_match($match_id);

            // todo: this contains much more information than needed, but is it much overhead?
            $match_players[$player1_id] = zone_util::players()->get_player($player1_id);
            $match_players[$player2_id] = zone_util::players()->get_player($player2_id);

            [$match] = zone_util::draw_teams()->make_matches($match_players);
            [$map_id, $match_civ_ids, $team1_civ_ids, $team2_civ_ids, $player_civ_ids] = zone_util::draw_settings()->draw_settings($match[0], $match[1]);
            return $this->create_match($add_user_id, $match[0], $match[1], $map_id, $match_civ_ids, $team1_civ_ids, $team2_civ_ids, $player_civ_ids);
        }
        else
        {
            return 0;
        }
    }

    public function remove_pair(int $remove_user_id, int $match_id, int $player1_id, int $player2_id): int
    {
        $match_players = $this->get_teams_players(...$this->get_match_team_ids($match_id));

        if(\count($match_players) > 2)
        {
            $this->clean_match($match_id);

            unset($match_players[$player1_id], $match_players[$player2_id]);

            [$match] = zone_util::draw_teams()->make_matches($match_players);
            [$map_id, $match_civ_ids, $team1_civ_ids, $team2_civ_ids, $player_civ_ids] = zone_util::draw_settings()->draw_settings($match[0], $match[1]);
            return $this->create_match($remove_user_id, $match[0], $match[1], $map_id, $match_civ_ids, $team1_civ_ids, $team2_civ_ids, $player_civ_ids);
        }
        else
        {
            return 0;
        }
    }

    public function clean_match(int $match_id): void
    {
        // note: team ids where certainly be fetched before, but I rather have the match id as an argument only
        $team_ids = $this->get_match_team_ids($match_id);

        $where_match_id = ' WHERE `match_id` = ' . $match_id;
        $where_team_id = ' WHERE ' . $this->db->sql_in_set('team_id', $team_ids);
        $this->db->sql_query('DELETE FROM ' . $this->match_players_table . $where_team_id);
        $this->db->sql_query('DELETE FROM ' . $this->match_civs . $where_match_id);
        $this->db->sql_query('DELETE FROM ' . $this->team_civs . $where_team_id);
        $this->db->sql_query('DELETE FROM ' . $this->match_player_civs . $where_match_id);
        $this->db->sql_query('DELETE FROM ' . $this->match_teams_table . $where_team_id);
        $this->db->sql_query('DELETE FROM ' . $this->matches_table . $where_match_id);
    }

    public function post(int $match_id, int $post_user_id, int $winner): void
    {
        // todo: maybe check here if the game was already posted? or leave it the function which calls this
        if($winner === 1 || $winner === 2)
        {
            $map_id = (int)db_util::get_var($this->db, [
                'SELECT' => 't.map_id',
                'FROM' => [$this->matches_table => 't'],
                'WHERE' => 't.match_id = ' . $match_id
            ]);

            [$team1_id, $team2_id] = $this->get_match_team_ids($match_id);
            $winner_team_id = ($winner === 1) ? $team1_id : $team2_id;
            $match_players = $this->get_teams_players($team1_id, $team2_id);
            $match_size = \count($match_players) / 2;
            $match_points = $this->get_match_points($match_size);


            $match_civ_ids = db_util::get_col($this->db, [
                'SELECT' => 't.civ_id',
                'FROM' => [$this->match_civs_table => 't'],
                'WHERE' => 't.match_id = ' . $match_id,
            ]);

            $team1_civ_ids = [];
            $team2_civ_ids = [];
            $team_civ_rows = db_util::get_rows($this->db, [
                'SELECT' => 't.civ_id, t.team_id',
                'FROM' => [$this->team_civs_table => 't'],
                'WHERE' => 't.team_id = ' . $team1_id . ' OR t.team_id = ' . $team2_id,
            ]);
            foreach($team_civ_rows as $r)
            {
                if($r['team_id'] == $team1_id)
                {
                    $team1_civ_ids = (int)$r['$civ_id'];
                }
                else
                {
                    $team2_civ_ids = (int)$r['$civ_id'];
                }
            }

            $player_civ_ids = [];
            $player_civ_rows = db_util::get_rows($this->db, [
                'SELECT' => 't.civ_id, t.user_id',
                'FROM' => [$this->match_player_civs_table => 't'],
                'WHERE' => 't.match_id = ' . $match_id
            ]);
            foreach($player_civ_rows as $r)
            {
                $player_civ_ids[(int)$r['user_id']] = (int)$r['civ_id'];
            }

            $players = zone_util::players();
            $user_ids = [];
            foreach($match_players as $user_id => $user_info)
            {
                $user_ids[] = $user_id;

                $civ_ids = array_merge(
                    $match_civ_ids,
                    $user_info['team_id'] == $team1_id ? $team1_civ_ids : $team2_civ_ids,
                    array_key_exists($user_id, $player_civ_ids) ? [$player_civ_ids[$user_id]] : []
                );

                db_util::update($this->db, $this->player_civ_table, ['time' => time()], $this->db->sql_in_set('civ_id', $civ_ids) . ' AND `user_id` = ' . $user_id);

                $players->match_changes($user_id, $match_points, $user_info['team_id'] == $winner_team_id);
            }
            db_util::update($this->db, $this->match_players_table, ['rating_change' => ($winner == 1 ? 1 : -1) * $match_points], ['team_id' => $team1_id]);
            db_util::update($this->db, $this->match_players_table, ['rating_change' => ($winner == 2 ? 1 : -1) * $match_points], ['team_id' => $team2_id]);

            db_util::update($this->db, $this->player_map_table, ['time' => time()], $this->db->sql_in_set('user_id', $user_ids) . ' AND `map_id` = ' . $map_id);

            $this->evaluate_bets($team1_id, $team2_id, time());
        }
        else
        {
            $winner_team_id = 0;
        }

        db_util::update($this->db, $this->matches_table, [
            'post_user_id' => $post_user_id,
            'post_time' => time(),
            'winner_team_id' => $winner_team_id,
        ], [
            'match_id' => $match_id
        ]);
    }

    public function get_match_team_ids(int $match_id): array
    {
        $team_rows = db_util::get_rows($this->db, [
            'SELECT' => 't.team_id AS team_id',
            'FROM' => [$this->match_teams_table => 't'],
            'WHERE' => 't.match_id = ' . $match_id,
        ]);
        // todo: check if these teams exists and do something if not
        return [(int)$team_rows[0]['team_id'], (int)$team_rows[1]['team_id']];
    }

    public function get_teams_players(int ...$team_ids): array
    {
        if(\count($team_ids) === 0) {
            return [];
        }

        $rows = db_util::get_rows($this->db, [
            'SELECT' => 't.team_id, t.user_id, t.draw_rating as rating',
            'FROM' => [$this->match_players_table => 't'],
            'WHERE' => $this->db->sql_in_set('t.team_id', $team_ids)
        ]);

        $teams_players = [];
        foreach ($rows as $r) {
            $teams_players[(int)$r['user_id']] = ['team_id' => (int)$r['team_id'], 'rating' => (int)$r['rating']];
        }

        return $teams_players;
    }

    public function get_match_points(int $match_size): int
    {
        /**
         * todo: the draw algorithm should be able to compensate almost everything, but we also
         * should cover other cases
         * also: read this from config?
         */
        switch($match_size)
        {
            case 1: return 0;
            case 2: return 6;
            case 3: return 12;
            case 4: return 9;
            default: return 0; // maybe change this in case fwb comes back and plays some 1v5
        }
    }


    public function evaluate_bets(int $winner_team, int $loser_team, int $end_time): void
    {
        $config = phpbb_util::config();

        $users_right = db_util::get_col($this->db, [
            'SELECT' => 't.user_id',
            'FROM' => [$this->bets_table => 't'],
            'WHERE' => 't.team_id = '. $winner_team .' AND t.time <= ' . ((int)$config['nczone_bet_time'] - 1200),
        ]);
        $users_wrong = db_util::get_col($this->db, [
            'SELECT' => 't.user_id',
            'FROM' => [$this->bets_table => 't'],
            'WHERE' => 't.team_id = '. $loser_team .' AND t.time <= ' . ((int)$config['nczone_bet_time'] - 1200),
        ]);

        if($$users_right)
        {
            $this->db->sql_query('UPDATE ' . $this->players_table . ' SET `bets_won` = `bets_won` + 1 WHERE ' . $this->db->sql_in_set('user_id', $users_right));
        }
        if($users_wrong)
        {
            $this->db->sql_query('UPDATE ' . $this->players_table . ' SET `bets_loss` = `bets_loss` + 1 WHERE ' . $this->db->sql_in_set('user_id', $users_wrong));
        }
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
    public function create_match(int $draw_user_id, array $team1, array $team2, int $map_id=0,
                                 array $match_civ_ids=[], array $team1_civ_ids=[], array $team2_civ_ids=[],
                                 array $player_civ_ids=[]): int
    {
        $match_id = (int)db_util::insert($this->db, $this->matches_table, [
            'match_id' => 0,
            'map_id' => $map_id,
            'draw_user_id' => $draw_user_id,
            'post_user_id' => 0,
            'draw_time' => time(),
            'post_time' => 0,
            'winner_team_id' => 0,
        ]);

        $team1_id = $this->insert_new_match_team($match_id);
        $team2_id = $this->insert_new_match_team($match_id);

        $team_data = [];
        foreach($team1 as $player)
        {
            $team_data[] = [
                'team_id' => $team1_id,
                'user_id' => $player['id'],
                'draw_rating' => $player['rating'],
                'rating_change' => 0,
            ];
        }
        foreach($team2 as $player)
        {
            $team_data[] = [
                'team_id' => $team2_id,
                'user_id' => $player['id'],
                'draw_rating' => $player['rating'],
                'rating_change' => 0,
            ];
        }
        if(!empty($team_data))
        {
            $this->db->sql_multi_insert($this->match_players_table, $team_data);
        }


        $match_civ_data = [];
        $match_civ_numbers = array_count_values($match_civ_ids);
        $unique_match_civ_ids = array_unique($match_civ_ids);
        foreach($unique_match_civ_ids as $civ_id)
        {
            $match_civ_data[] = [
                'match_id' => $match_id,
                'civ_id' => $civ_id,
                'number' => $match_civ_numbers[$civ_id],
            ];
        }
        if(!empty($match_civ_data))
        {
            $this->db->sql_multi_insert($this->match_civs_table, $match_civ_data);
        }


        $team_civ_data = [];
        $team1_civ_numbers = array_count_values($team1_civ_ids);
        foreach(array_unique($team1_civ_ids) as $civ_id)
        {
            $team_civ_data[] = [
                'team_id' => $team1_id,
                'civ_id' => (int)$civ_id,
                'number' => $team1_civ_numbers[$civ_id],
            ];
        }
        $team2_civ_numbers = array_count_values($team2_civ_ids);
        foreach(array_unique($team2_civ_ids) as $civ_id)
        {
            $team_civ_data[] = [
                'team_id' => $team2_id,
                'civ_id' => (int)$civ_id,
                'number' => $team2_civ_numbers[$civ_id],
            ];
        }
        if(!empty($team_civ_data))
        {
            $this->db->sql_multi_insert($this->team_civs_table, $team_civ_data);
        }


        $player_civ_data = [];
        foreach($player_civ_ids as $user_id => $civ_id)
        {
            $player_civ_data[] = [
                'match_id' => $match_id,
                'user_id' => (int)$user_id,
                'civ_id' => (int)$civ_id,
            ];
        }
        if(!empty($player_civ_data))
        {
            $this->db->sql_multi_insert($this->match_player_civs_table, $player_civ_data);
        }

        return $match_id;
    }


    private function insert_new_match_team(int $match_id): int
    {
        return (int)db_util::insert($this->db, $this->match_teams_table, [
            'team_id' => 0,
            'match_id' => $match_id,
        ]);
    }

    public function get_all_rmatches(): array
    {
        $rmatches = [];

        $match_rows = db_util::get_rows($this->db, [
            'SELECT' => 't.match_id, t.map_id, m.map_name, t.draw_user_id, u.username AS draw_username, t.draw_time',
            'FROM' => [$this->matches_table => 't', $this->maps_table => 'm', $this->users_table => 'u'],
            'WHERE' => '(t.map_id = m.map_id OR t.map_id = 0) AND t.draw_user_id = u.user_id AND t.post_user_id = 0',
            'ORDER_BY' => 't.draw_time DESC'
        ]);
        foreach($match_rows as $m)
        {
            $match_id = (int)$m['match_id'];
            [$team1_id, $team2_id] = $this->get_match_team_ids($match_id);

            $match = [
                'id' => $match_id,
                'game_type' => 'IP', // todo: remove
                'ip' => '', // todo: remove
                'timestampStart' => (int)$m['draw_time'],
                'timestampEnd' => 0,
                'winner' => 0,
                'whiner' => 'Snyper',
                'result_poster' => NULL,
                'drawer' => [
                    'id' => (int)$m['draw_user_id'],
                    'name' => $m['draw_username'],
                ],
                'map' => [
                    'id' => (int)$m['map_id'],
                    'title' => $m['map_name'],
                ],
                'civs' => array_merge(['both' => $this->get_match_civs($match_id)], $this->get_team_civs($team1_id, $team2_id)),
                'bets' => ['team1' => [], 'team2' => []],
                'players' => zone_util::players()->get_match_players($match_id, $team1_id, $team2_id),
            ];
            $rmatches[] = $match;
        }

        return $rmatches;
    }

    public function get_all_pmatches(): array
    {
        $rmatches = [];
        # todo implement..
        return $rmatches;
    }

    public function get_match_civs(int $match_id): array
    {
        $rows = db_util::get_rows($this->db, [
            'SELECT' => 'm.civ_id AS id, c.civ_name AS title, mc.multiplier, m.number',
            'FROM' => [$this->match_civs_table => 'm', $this->civs_table => 'c', $this->map_civs_table => 'mc'],
            'WHERE' => 'm.civ_id = c.civ_id AND m.civ_id = mc.civ_id AND m.match_id = ' . $match_id,
            'ORDER_BY' => 'mc.multiplier DESC'
        ]);

        return array_map(function($row) {
            return [
                'id' => (int)$row['id'],
                'title' => $row['title'],
                'number' => (int)$row['number'],
            ];
        }, $rows);
    }

    public function get_team_civs(int $team1_id, int $team2_id): array
    {
        $rows = db_util::get_rows($this->db, [
            'SELECT' => 'm.team_id, m.civ_id AS id, c.civ_name AS title, mc.multiplier, m.number',
            'FROM' => [$this->team_civs_table => 'm', $this->civs_table => 'c', $this->map_civs_table => 'mc'],
            'WHERE' => 'm.civ_id = c.civ_id AND m.civ_id = mc.civ_id AND (m.team_id = ' . $team1_id . ' OR m.team_id = ' . $team2_id . ')',
            'ORDER_BY' => 'mc.multiplier DESC'
        ]);

        $teams = ['team1' => [], 'team2' => []];
        foreach($rows as $r)
        {
            $team_id = (int)$r['team_id'];
            unset($r['team_id']);

            $r['civ_id'] = (int)$r['civ_id'];
            $r['number'] = (int)$r['number'];

            $teams[($team_id === $team1_id) ? 'team1' : 'team2'][] = $r;
        }

        return $teams;
    }

    public function get_bets(int $team1_id, int $team2_id): array
    {
        $rows = db_util::get_rows($this->db, [
            'SELECT' => 'b.user_id, b.team_id, b.time, u.username',
            'FROM' => [$this->bets_table => 'b', $this->users_table => 'u'],
            'WHERE' => 'b.user_id = u.user_id AND (b.team_id = ' . $team1_id . ' OR b.team_id = ' . $team2_id . ')'
        ]);

        $bets = ['team1' => [], 'team2' => []];
        foreach($rows as $r)
        {
            $bets[($r['team_id'] === $team1_id) ? 'team1' : 'team2'][] = [
                'timestamp' => (int)$r['time'],
                'user' => [
                    'id' => (int)$r['user_id'],
                    'name' => $r['username']
                ]
            ];
        }

        return $bets;
    }

    private function check_draw_process(int $user_id=0): int
    {
        $row = db_util::get_row($this->db, [
            'SELECT' => 't.draw_id, t.time',
            'FROM' => [$this->draw_process_table => 't'],
            'WHERE' => $user_id ? ('t.user_id = ' . $user_id) : '1',
        ]);
        if($row)
        {
            $draw_id = (int)$row['draw_id'];
            $draw_process_time = (int)$row['time'];
        }
        else
        {
            return 0;
        }

        if($draw_process_time && time() - $draw_process_time > 60)
        {
            $this->db->sql_query('TRUNCATE `' . $this->draw_process_table . '`');
            $this->db->sql_query('TRUNCATE `' . $this->draw_players_table . '`');
            return 0;
        }

        return (int)$draw_id;
    }

    private function get_draw_players(int $draw_id): array
    {
        $rows = db_util::get_rows($this->db, [
            'SELECT' => 'p.user_id AS id, u.username, p.rating',
            'FROM' => [$this->draw_players_table => 'd', $this->players_table => 'p', $this->users_table => 'u'],
            'WHERE' => 'd.draw_id = ' . $draw_id . ' AND d.user_id = p.user_id AND p.user_id = u.user_id',
        ]);
        return array_map(function($row) {
            return [
                'id' => (int)$row['id'],
                'username' => $row['username'], // todo: probably not needed
                'rating' => (int)$row['rating'],
            ];
        }, $rows);
    }

    public function start_draw_process(int $user_id): array
    {
        $this->db->sql_query('LOCK TABLES `' . $this->draw_process_table . '` WRITE, `' . $this->draw_process_table . '` AS `t` WRITE, `' . $this->draw_players_table . '` WRITE, `'. $this->players_table .'` AS `p` READ, `'. $this->users_table .'` AS `u` READ');

        if($this->check_draw_process())
        {
            $this->db->sql_query('UNLOCK TABLES');
            return [];
        }

        $logged_in = zone_util::players()->get_logged_in();
        if(\count($logged_in) < 2)
        {
            $this->db->sql_query('UNLOCK TABLES');
            return [];
        }

        $draw_id = (int)db_util::insert($this->db, $this->draw_process_table, [
            'draw_id' => 0,
            'user_id' => $user_id,
            'time' => time(),
        ]);

        $sql_array = [];
        foreach($logged_in as $u)
        {
            $sql_array[] = [
                'draw_id' => $draw_id,
                'user_id' => $u['id'],
            ];
        }
        $this->db->sql_multi_insert($this->draw_players_table, $sql_array);

        $this->db->sql_query('UNLOCK TABLES');
        return $logged_in;
    }

    public function confirm_draw_process(int $user_id): array
    {
        $this->db->sql_query('LOCK TABLES `' . $this->draw_process_table . '` WRITE, `' . $this->draw_process_table . '` AS `t` WRITE, `' . $this->draw_players_table . '` WRITE, `' . $this->draw_players_table . '` AS `d` READ, `'. $this->players_table .'` AS `p` READ, `'. $this->users_table .'` AS `u` READ');

        $draw_id = $this->check_draw_process($user_id);
        if(!$draw_id)
        {
            $this->db->sql_query('UNLOCK TABLES');
            return [];
        }

        $draw_players = $this->get_draw_players($draw_id);

        $this->db->sql_query('TRUNCATE `' . $this->draw_process_table . '`');
        $this->db->sql_query('TRUNCATE `' . $this->draw_players_table . '`');
        $this->db->sql_query('UNLOCK TABLES');

        return $draw_players;
    }

    public function deny_draw_process(int $user_id): void
    {
        $this->db->sql_query('LOCK TABLES `' . $this->draw_process_table . '` WRITE, `' . $this->draw_process_table . '` AS `t` WRITE, `' . $this->draw_players_table . '` WRITE');

        if($this->check_draw_process($user_id))
        {
            $this->db->sql_query('TRUNCATE `' . $this->draw_process_table . '`');
            $this->db->sql_query('TRUNCATE `' . $this->draw_players_table . '`');
        }

        $this->db->sql_query('UNLOCK TABLES');
    }
}
