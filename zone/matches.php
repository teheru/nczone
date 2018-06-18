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
use phpbb\db\driver\driver_interface;

/**
 * nC Zone matches management class.
 */
class matches {

    const MATCH_CIVS = 'match_civs';
    const PLAYER_CIVS = 'player_civs';
    const TEAM_CIVS = 'team_civs';

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
     * @param string                             $maps_table               Name of the civs table
     * @param string                             $player_map_table         Name of the table with the last time a player played a map
     * @param string                             $map_civs_table           Name of the table which contains civ information for maps
     * @param string                             $player_civ_table         Name of the table which contains the last time a player played a civ
     * @param string                             $bets_table               Name of the bets table
     * @param string                             $users_table              Name of the phpBB users table
     */
    public function __construct(driver_interface $db, string $matches_table, string $match_teams_table,
                                string $match_players_table, string $match_civs_table, string $team_civs_table,
                                string $match_player_civs_table, string $maps_table, string $civs_table, string $player_map_table,
                                string $map_civs_table, string $player_civ_table, string $bets_table, string $users_table)
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
        $this->bets_table = $bets_table;
        $this->users_table = $users_table;
    }


    public function draw(int $draw_user_id): array
    {
        $match_ids = [];
        $user_ids = [];

        $players = zone_util::players();
        $logged_in = $players->get_logged_in();

        if(\count($logged_in) < 2) {
            return [];
        }

        $matches = zone_util::draw()->make_matches($logged_in);
        foreach ($matches as $match) {
            [$map_id, $match_civ_ids, $team1_civ_ids, $team2_civ_ids, $player_civ_ids]
                = $this->draw_settings($match[0], $match[1]);
            $match_ids[] = $this->create_match($draw_user_id, $match[0], $match[1], $map_id, $match_civ_ids,
                $team1_civ_ids, $team2_civ_ids, $player_civ_ids);

            foreach (array_merge($match[0], $match[1]) as $player) {
                $user_ids[] = (int)$player['id'];
            }
        }

        $players->logout_players($user_ids);

        return $match_ids;
    }

    public function draw_settings(array $team1, array $team2): array
    {
        $players = array_merge($team1, $team2);

        $match_civ_ids = [];
        $team1_civ_ids = [];
        $team2_civ_ids = [];
        $player_civ_ids = [];
        $map_id = $this->get_players_map_id($players);

        $civ_kind = $this->decide_draw_civs_kind($team1, $team2);
        if($civ_kind === self::MATCH_CIVS)
        {
            $match_civs = $this->draw_match_civs($map_id, $players);
            foreach($match_civs as $civ)
            {
                $match_civ_ids[] = $civ['id'];
            }
        }
        elseif($civ_kind === self::TEAM_CIVS)
        {
            [$team1_civs, $team2_civs] = $this->draw_teams_civs($map_id, $team1, $team2);
            foreach($team1_civs as $civ)
            {
                $team1_civ_ids[] = $civ['id'];
            }
            foreach($team2_civs as $civ)
            {
                $team2_civ_ids[] = $civ['id'];
            }
        }
        else
        {
            $player_civs = $this->draw_player_civs($map_id, $team1, $team2);
            foreach($player_civs as $user_id => $pc)
            {
                $player_civ_ids[$user_id] = $pc['id'];
            }
        }

        return [$map_id, $match_civ_ids, $team1_civ_ids, $team2_civ_ids, $player_civ_ids];
    }

    public function replace_player(int $replace_user_id, int $match_id, int $player1_id, int $player2_id): int
    {
        $match_players = $this->get_teams_players(...$this->get_match_team_ids($match_id));

        if(array_key_exists($player1_id, $match_players) && !array_key_exists($player2_id, $match_player))
        {
            $this->clean_match($match_id);

            unset($match_players[$player1_id]);
            // todo: this contains much more information than needed, but is it much overhead?
            $match_players[$player2_id] = zone_util::players()->get_player($player2_id);

            [$match] = zone_util::draw()->make_matches($match_players);
            [$map_id, $match_civ_ids, $team1_civ_ids, $team2_civ_ids, $player_civ_ids] = $this->draw_settings($match[0], $match[1]);
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

            [$match] = zone_util::draw()->make_matches($match_players);
            [$map_id, $match_civ_ids, $team1_civ_ids, $team2_civ_ids, $player_civ_ids] = $this->draw_settings($match[0], $match[1]);
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

            [$match] = zone_util::draw()->make_matches($match_players);
            [$map_id, $match_civ_ids, $team1_civ_ids, $team2_civ_ids, $player_civ_ids] = $this->draw_settings($match[0], $match[1]);
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
                    array_key_exists($user_id, $player_civ_ids) ? $player_civ_ids[$user_id] : []
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
        $users_right = db_util::get_col($this->db, [
            'SELECT' => 't.user_id',
            'FROM' => [$this->bets_table => 't'],
            'WHERE' => 't.team_id = '. $winner_team .' AND t.time <= ' . ($end_time - 1200), // todo: replace by ACP var
        ]);
        $users_wrong = db_util::get_col($this->db, [
            'SELECT' => 't.user_id',
            'FROM' => [$this->bets_table => 't'],
            'WHERE' => 't.team_id = '. $loser_team .' AND t.time <= ' . ($end_time - 1200), // todo: replace by ACP var
        ]);

        $this->db->sql_query('UPDATE ' . $this->players_table . ' SET `bets_won` = `bets_won` + 1 WHERE ' . $this->db->sql_in_set('user_id', $users_right));
        $this->db->sql_query('UPDATE ' . $this->players_table . ' SET `bets_loss` = `bets_loss` + 1 WHERE ' . $this->db->sql_in_set('user_id', $users_wrong));
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

    /**
     * Calculates the next map (id) to be drawn for a group of players
     *
     * @param array  $users
     *
     * @return int
     */
    public function get_players_map_id(array $users): int
    {
        $user_ids = [];
        foreach($users as $user)
        {
            $user_ids[] = $user['id'];
        }
        $user_num = \count($user_ids);

        $rows = db_util::get_rows($this->db, [
            'SELECT' => 't.map_id, SUM(' . time() . ' - t.time) * m.weight AS val',
            'FROM' => [$this->player_map_table => 't', $this->maps_table => 'm'],
            'WHERE' => 't.map_id = m.map_id AND ' . $this->db->sql_in_set('t.user_id', $user_ids),
            'GROUP_BY' => 't.map_id, t.user_id',
            'ORDER_BY' => 'val DESC'
        ]);

        $counter = [];
        foreach($rows as $r)
        {
            if(!array_key_exists($r['map_id'], $counter))
            {
                $counter[$r['map_id']] = 1;
            }
            else
            {
                $counter[$r['map_id']]++;
            }

            if($counter[$r['map_id']] === $user_num)
            {
                return (int)$r['map_id'];
            }
        }

        return 0;
    }

    public function decide_draw_civs_kind(array $team1_users, array $team2_users): string
    {
        $min_max_diff = number_util::diff(
            players::get_max_rating($team1_users, $team2_users),
            players::get_min_rating($team1_users, $team2_users)
        );

        // todo: replace this values by ACP config values
        if ($min_max_diff >= 600) {
            return self::PLAYER_CIVS;
        }

        $rating_sum_diff = number_util::diff(
            players::get_rating_sum($team1_users),
            players::get_rating_sum($team2_users)
        );

        // todo: replace this values by ACP config values
        if($rating_sum_diff >= 120) {
            return self::TEAM_CIVS;
        }

        return self::MATCH_CIVS;
    }

    protected function draw_players_civs(int $map_id, array $users, int $num_civs, int $extra_civs, bool $ignore_force=False): array
    {
        $civ_ids = [];
        $civ_multiplier = [];

        $user_ids = [];
        foreach($users as $user)
        {
            $user_ids[] = $user['id'];
        }

        // first, get one of the force draw civs
        $force_civ_num = 0;
        $sql_add = '';
        $test_civ_add = [];
        $force_civ = [];
        if(!$ignore_force)
        {
            $force_civ = db_util::get_row($this->db, [
                'SELECT' => 'c.civ_id AS id, c.multiplier AS multiplier',
                'FROM' => array($this->map_civs_table => 'c', $this->player_civ_table => 'p'),
                'WHERE' => 'c.civ_id = p.civ_id AND c.force_draw AND NOT c.prevent_draw AND c.map_id = ' . $map_id . ' AND '. $this->db->sql_in_set('p.user_id', $user_ids),
                'GROUP_BY' => 'c.civ_id',
                'ORDER_BY' => 'SUM(' . time() . ' - p.time) DESC',
            ]);
            if($force_civ)
            {
                $force_civ_num = 1;
                $sql_add = ' AND c.civ_id != ' . $force_civ['id'];
                $test_civ_add = [$force_civ];
            }
        }

        // get additional civs
        $draw_civs = db_util::get_num_rows($this->db, [
            'SELECT' => 'c.civ_id AS id, c.multiplier AS multiplier',
            'FROM' => array($this->map_civs_table => 'c', $this->player_civ_table => 'p'),
            'WHERE' => 'c.civ_id = p.civ_id AND NOT c.prevent_draw AND c.map_id = ' . $map_id . $sql_add,
            'GROUP_BY' => 'c.civ_id',
            'ORDER_BY' => 'SUM(' . time() . ' - p.time) DESC',
        ], $num_civs - \count($civ_ids) + $extra_civs);

        if($extra_civs === 0)
        {
            $best_civs = $draw_civs;
        }
        else
        {
            // we drawed some extra civs and now we drop some to reduce the difference of multipliers
            $best_civs = [];
            $best_value = -1;
            for($i = 0; $i < $extra_civs; $i++)
            {
                $test_civs = \array_slice($draw_civs, $i, $num_civs - $force_civ_num);
                $test_civs_calc = array_merge($test_civ_add, $test_civs);
                $test_civs_calc = civs::sort_by_multiplier($test_civs_calc);
                $value = $test_civs_calc[0]['multiplier'] - end($test_civs_calc)['multiplier'];
                reset($test_civs_calc);
                if($value < $best_value || $best_value < 0)
                {
                    $best_civs = $test_civs;
                    $best_value = $value;
                }
            }
        }

        return [$force_civ ?: NULL, $best_civs];
    }

    public function draw_match_civs(int $map_id, array $users, int $num_civs=0, int $extra_civs=4): array
    {
        $players_civs = $this->draw_players_civs($map_id, $users, $num_civs ?: \count($users) / 2, $extra_civs);

        $drawed_civs = $players_civs[0]
            ? array_merge([$players_civs[0]], $players_civs[1])
            : $players_civs[1];

        return civs::sort_by_multiplier($drawed_civs);
    }

    public function draw_teams_civs(int $map_id, array $team1_users, array $team2_users, int $num_civs=0, int $extra_civs=2): array
    {
        if($num_civs === 0)
        {
            $num_civs = \count($team1_users);
        }


        $team1_sum_rating = players::get_rating_sum($team1_users);
        $team2_sum_rating = players::get_rating_sum($team2_users);


        // find out which civs have to be in both teams
        $both_teams_civs = zone_util::maps()->get_map_both_teams_civ_ids($map_id);


        // we use some extra civs to be able to draw fair civs for the teams
        $team1_players_civs = $this->draw_players_civs($map_id, $team1_users, $num_civs + $extra_civs, 0);
        $team1_force_civ = $team1_players_civs[0];
        $team1_civpool = $team1_players_civs[1];

        // if the force civ must be in both teams, we don't want to draw another one for team 2
        $ignore_force = False;
        if($team1_force_civ)
        {
            $num_civs--;

            if(\in_array($team1_force_civ['id'], $both_teams_civs))
            {
                $ignore_force = True;
            }
        }

        $team2_players_civs = $this->draw_players_civs($map_id, $team2_users, $num_civs + $extra_civs, 0, $ignore_force);
        $team2_force_civ = $team2_players_civs[0];
        if($ignore_force)
        {
            $team2_force_civ = $team1_force_civ;
        }
        $team2_civpool = $team2_players_civs[1];


        // create a common civpool for civs which has to be in both teams
        $both_civpool = [];
        foreach($team1_civpool as $key => $civ)
        {
            if(\in_array($civ['id'], $both_teams_civs))
            {
                $both_civpool[] = $civ;
                unset($team1_civpool[$key]);
            }
        }
        foreach($team2_civpool as $key => $civ)
        {
            if(\in_array($civ['id'], $both_teams_civs))
            {
                $both_civpool[] = $civ;
                unset($team2_civpool[$key]);
            }
        }

        // remove extra civs from either team civpools
        $diff_num = \count($team1_civpool) - \count($team2_civpool);
        if($diff_num > 0)
        {
            for($i = 0; $i < $diff_num; $i++)
            {
                array_pop($team1_civpool);
            }
        }
        else
        {
            for($i = 0; $i < -$diff_num; $i++)
            {
                array_pop($team2_civpool);
            }
        }
        $unique_civpool_num = \count($team1_civpool);


        // get all index combinations with length $num_civs for our civpools
        $test_indices = [];
        for($i = 0; $i < $extra_civs + 1; $i++)
        {
            $test_indices[] = [$i];
        }
        for($i = 1; $i < $num_civs; $i++)
        {
            $temp = [];
            foreach($test_indices as $lo)
            {
                for($j = end($lo)+1; $j < $num_civs + $extra_civs; $j++)
                {
                    $temp[] = array_merge($lo, [$j]);
                }
            }
            $test_indices = $temp;
        }


        $team1_best_indices = [];
        $team2_best_indices = [];
        $best_value = -1;

        // we test all possible combinations and take the combination, which minimizes |diff(player_ratings * multipliers)|
        foreach($test_indices as $team1_indices)
        {
            $both_civs_indices = [];

            $team1_sum_multiplier = 0;
            foreach($team1_indices as $index)
            {
                if($index < $unique_civpool_num)
                {
                    $team1_sum_multiplier += $team1_civpool[$index]['multiplier'];
                }
                else
                {
                    $team1_sum_multiplier += $both_civpool[$index - $unique_civpool_num]['multiplier'];
                    $both_civs_indices[] = $index;
                }
            }
            if($team1_force_civ)
            {
                $team1_sum_multiplier += $team1_force_civ['multiplier'];
            }

            foreach($test_indices as $team2_indices)
            {
                foreach($both_civs_indices as $index)
                {
                    if(!\in_array($index, $team2_indices))
                    {
                        continue 2;
                    }
                }

                $team2_sum_multiplier = 0;
                foreach($team2_indices as $index)
                {
                    if($index < $unique_civpool_num)
                    {
                        $team2_sum_multiplier += $team2_civpool[$index]['multiplier'];
                    }
                    else
                    {
                        $team2_sum_multiplier += $both_civpool[$index - $unique_civpool_num]['multiplier'];
                    }
                }
                if($team2_force_civ)
                {
                    $team2_sum_multiplier += $team2_force_civ['multiplier'];
                }

                $value = number_util::diff(
                    $team1_sum_rating * $team1_sum_multiplier,
                    $team2_sum_rating * $team2_sum_multiplier
                );
                if($value < $best_value || $best_value < 0)
                {
                    $team1_best_indices = $team1_indices;
                    $team2_best_indices = $team2_indices;
                    $best_value = $value;
                }
            }
        }


        // apply the indices on the civpools
        $team1_civs = [];
        $team2_civs = [];
        foreach($team1_best_indices as $index)
        {
            $team1_civs[] = $index < $unique_civpool_num
                ? $team1_civpool[$index]
                : $both_civpool[$index - $unique_civpool_num];
        }
        foreach($team2_best_indices as $index)
        {
            $team2_civs[] = $index < $unique_civpool_num
                ? $team2_civpool[$index]
                : $both_civpool[$index - $unique_civpool_num];
        }

        // and add the civs forced by the map
        if($team1_force_civ)
        {
            $team1_civs[] = $team1_force_civ;
        }
        if($team2_force_civ)
        {
            $team2_civs[] = $team2_force_civ;
        }

        return [
            civs::sort_by_multiplier($team1_civs),
            civs::sort_by_multiplier($team2_civs),
        ];
    }

    public function draw_player_civs(int $map_id, array $team1_users, array $team2_users, int $num_civs=3): array
    {
        $civs_module = zone_util::civs();

        // find out which civs have to be in both teams
        $both_teams_civ_ids = zone_util::maps()->get_map_both_teams_civ_ids($map_id);

        $user_ids = [];
        $team1_ids = [];
        $team2_ids = [];
        $user_ratings = [];
        $user_civpools = [];
        foreach($team1_users as $user)
        {
            $user_ids[] = $user['id'];
            $team1_ids[] = $user['id'];
            $user_ratings[$user['id']] = $user['rating'];
            $user_civpools[$user['id']] = [];
        }
        foreach($team2_users as $user)
        {
            $user_ids[] = $user['id'];
            $team2_ids[] = $user['id'];
            $user_ratings[$user['id']] = $user['rating'];
            $user_civpools[$user['id']] = [];
        }


        // get civs which are forced for the map
        $force_civ_ids = [];

        $team1_force_civ = $civs_module->get_map_players_single_civ($map_id, $team1_ids, [], False, True);
        if($team1_force_civ)
        {
            // check if the civ has to be in both teams anyways
            if(\in_array($team1_force_civ['civ_id'], $both_teams_civ_ids))
            {
                $team2_force_civ = $civs_module->get_map_players_single_civ($map_id, $team2_ids, [$team1_force_civ['civ_id']], False, True);
            }
            else
            {
                // check force civ for the other team except the one drawed before
                $team2_force_civ = $civs_module->get_map_players_single_civ($map_id, $team2_ids, [$team1_force_civ['civ_id']], True, True);
                if($team2_force_civ)
                {
                    // check again if THIS civ has to be in both teams
                    if(\in_array($team2_force_civ['civ_id'], $both_teams_civ_ids))
                    {
                        $team1_force_civ = $civs_module->get_map_players_single_civ($map_id, $team1_ids, [$team2_force_civ['civ_id']], False, True);
                    }
                }
                // if this didn't work, we only have one forced civ and it has to be in both teams nevertheless
                else
                {
                    $team2_force_civ = $civs_module->get_map_players_single_civ($map_id, $team2_ids, [$team1_force_civ['civ_id']], False, True);
                }
            }

            // we need to remember these civs so we don't drop them or draw them again
            $force_civ_ids[] = $team1_force_civ['civ_id'];
            if($team1_force_civ['civ_id'] != $team2_force_civ['civ_id'])
            {
                $force_civ_ids[] = $team2_force_civ['civ_id'];
            }

            $user_civpools[$team1_force_civ['user_id']][] = ['id' => $team1_force_civ['civ_id'], 'multiplier' => $team1_force_civ['multiplier']];
            $user_civpools[$team2_force_civ['user_id']][] = ['id' => $team2_force_civ['civ_id'], 'multiplier' => $team2_force_civ['multiplier']];
        }
        // civs we don't want to drop
        $keep_civ_ids = array_merge($force_civ_ids, $both_teams_civ_ids);


        $user_civs = $civs_module->get_map_players_multiple_civs($map_id, $user_ids, $force_civ_ids, True, False);

        // remember civs that were already drawed so we don't draw them twice
        $civpool_civs = [];
        foreach($user_civs as $pc)
        {
            if(\in_array($pc['civ_id'], $civpool_civs)) {
                continue;
            }

            if (\count($user_civpools[$pc['user_id']]) >= $num_civs) {
                continue;
            }

            foreach ($user_civpools[$pc['user_id']] as $civ) {
                // we don't want to have multiple civs with same multiplier for the same player
                // also, don't add civs for players which have a civ which we want to keep
                if ($pc['multiplier'] == $civ['multiplier'] || \in_array($civ['id'], $keep_civ_ids)) {
                    continue 2;
                }
            }

            if (\in_array($pc['civ_id'], $both_teams_civ_ids)) {
                // if the drawed civ should be in both teams, get the player from the other team who should get that civ
                $other_team = $civs_module->get_map_players_single_civ($map_id, (\in_array($pc['user_id'], $team1_ids)) ? $team2_ids : $team1_ids, [$pc['civ_id']], False, False);
                if (\count($user_civpools[$other_team['user_id']]) > 0) {
                    foreach ($user_civpools[$other_team['user_id']] as $civ) {
                        // if he already has a forced civ or a civ for both teams, we drop the recently drawed civ
                        if (\in_array($civ['id'], $keep_civ_ids)) {
                            continue 2;
                        }
                    }
                }

                // overwrite the other players civs
                foreach ($user_civpools[$other_team['user_id']] as $civ) {
                    unset($civpool_civs[array_search($civ['id'], $civpool_civs)]);
                }
                $user_civpools[$other_team['user_id']] = [['id' => $other_team['civ_id'], 'multiplier' => $other_team['multiplier']]];
            }

            $civpool_civs[] = $pc['civ_id'];
            if (\in_array($pc['civ_id'], $both_teams_civ_ids)) {
                // overwrite this players civs
                foreach ($user_civpools[$pc['user_id']] as $civ) {
                    unset($civpool_civs[array_search($civ['id'], $civpool_civs)]);
                }
                $user_civpools[$pc['user_id']] = [['id' => $pc['civ_id'], 'multiplier' => $pc['multiplier']]];
            }
            else
            {
                $user_civpools[$pc['user_id']][] = ['id' => $pc['civ_id'], 'multiplier' => $pc['multiplier']];
            }
        }

        // calculate all possible combinations of civs for the players
        $civ_combinations = [];
        $first_user_id = array_shift($user_ids);
        foreach($user_civpools[$first_user_id] as $civ)
        {
            $civ_combinations[] = [$first_user_id => $civ];
        }

        foreach($user_ids as $user_id)
        {
            $new_civ_combinations = [];
            foreach($civ_combinations as $cc)
            {
                foreach($user_civpools[$user_id] as $civ)
                {
                    $temp = $cc;
                    $temp[$user_id] = $civ;
                    $new_civ_combinations[] = $temp;
                }
            }
            $civ_combinations = $new_civ_combinations;
        }


        // calculate sum rating * multiplier and minimize the abs difference for the teams
        $best_civ_combination = [];
        $best_diff = -1;
        foreach($civ_combinations as $cc)
        {
            $team1_sum = 0;
            $team2_sum = 0;
            foreach($cc as $user_id => $civ)
            {
                if(\in_array($user_id, $team1_ids))
                {
                    $team1_sum += $user_ratings[$user_id] * $civ['multiplier'];
                }
                else
                {
                    $team2_sum += $user_ratings[$user_id] * $civ['multiplier'];
                }
            }
            $diff = number_util::diff($team1_sum, $team2_sum);
            if($diff < $best_diff || $best_diff < 0)
            {
                $best_civ_combination = $cc;
                $best_diff = $diff;
            }
        }

        return $best_civ_combination;
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
            'WHERE' => 't.map_id = m.map_id AND t.draw_user_id = u.user_id AND t.post_time = 0',
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
            $bets[($team_id === $team1_id) ? 'team1' : 'team2'][] = [
                'timestamp' => (int)$r['time'],
                'user' => [
                    'id' => (int)$r['user_id'],
                    'name' => $r['username']
                ]
            ];
        }

        return $bets;
    }
}
