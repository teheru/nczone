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
use eru\nczone\utility\zone_util;
use eru\nczone\utility\phpbb_util;

/**
 * nC Zone matches management class.
 */
class matches {

    /** @var db */
    private $db;

    /**
     * Constructor
     *
     * @param db $db Database object
     */
    public function __construct(db $db)
    {
        $this->db = $db;
    }

    /**
     * @param int $draw_user_id
     * @param array $draw_players
     * @return array
     * @throws \Throwable
     */
    public function draw(int $draw_user_id, array $draw_players): array
    {
        if (\count($draw_players) < 2) {
            return [];
        }

        return $this->db->run_txn(function () use ($draw_user_id, $draw_players) {
            $match_ids = [];
            $user_ids = [];
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
            zone_util::players()->logout_players(...$user_ids);
            return $match_ids;
        });
    }

    /**
     * @param int $replace_user_id
     * @param int $match_id
     * @param int $player1_id
     * @param int $player2_id
     * @return int
     * @throws \Throwable
     */
    public function replace_player(int $replace_user_id, int $match_id, int $player1_id, int $player2_id): int
    {
        return $this->db->run_txn(function () use ($replace_user_id, $match_id, $player1_id, $player2_id) {
            $match_players = $this->get_match_players($match_id);
            if (array_key_exists($player1_id, $match_players) && !array_key_exists($player2_id, $match_players)) {
                unset($match_players[$player1_id]);
                $draw_players = [];
                $draw_players[] = zone_util::players()->get_player($player2_id);
                foreach ($match_players as $user_id => $mp) {
                    $mp['id'] = (int)$user_id;
                    $draw_players[] = $mp;
                }

                $this->clean_match($match_id);

                [$match] = zone_util::draw_teams()->make_matches($draw_players);
                [$map_id, $match_civ_ids, $team1_civ_ids, $team2_civ_ids, $player_civ_ids] = zone_util::draw_settings()->draw_settings($match[0], $match[1]);
                $match_id = $this->create_match($replace_user_id, $match[0], $match[1], $map_id, $match_civ_ids, $team1_civ_ids, $team2_civ_ids, $player_civ_ids);
                if ($match_id) {
                    zone_util::players()->logout_player($player1_id);
                    return $match_id;
                }
            }

            return 0;
        });
    }

    /**
     * @param int $add_user_id
     * @param int $match_id
     * @param int $player1_id
     * @param int $player2_id
     * @return int
     * @throws \Throwable
     */
    public function add_pair(int $add_user_id, int $match_id, int $player1_id, int $player2_id): int
    {
        return $this->db->run_txn(function () use ($add_user_id, $match_id, $player1_id, $player2_id) {
            $match_players = $this->get_match_players($match_id);
            if (!array_key_exists($player1_id, $match_players) && !array_key_exists($player2_id, $match_players) && \count($match_players) < 8) {
                $draw_players = [];
                $draw_players[] = zone_util::players()->get_player($player1_id);
                $draw_players[] = zone_util::players()->get_player($player2_id);
                foreach ($match_players as $user_id => $mp) {
                    $mp['id'] = (int)$user_id;
                    $draw_players[] = $mp;
                }

                $this->clean_match($match_id);

                [$match] = zone_util::draw_teams()->make_matches($draw_players);
                [$map_id, $match_civ_ids, $team1_civ_ids, $team2_civ_ids, $player_civ_ids] = zone_util::draw_settings()->draw_settings($match[0], $match[1]);

                return $this->create_match($add_user_id, $match[0], $match[1], $map_id, $match_civ_ids, $team1_civ_ids, $team2_civ_ids, $player_civ_ids);
            }

            return 0;
        });
    }

    /**
     * @param int $remove_user_id
     * @param int $match_id
     * @param int $player1_id
     * @param int $player2_id
     * @return int
     * @throws \Throwable
     */
    public function remove_pair(int $remove_user_id, int $match_id, int $player1_id, int $player2_id): int
    {
        return $this->db->run_txn(function () use ($remove_user_id, $match_id, $player1_id, $player2_id) {
            $match_players = $this->get_match_players($match_id);
            if (array_key_exists($player1_id, $match_players) && array_key_exists($player2_id, $match_players) && \count($match_players) > 2) {
                unset($match_players[$player1_id], $match_players[$player2_id]);
                $draw_players = [];
                foreach ($match_players as $user_id => $mp) {
                    $mp['id'] = (int)$user_id;
                    $draw_players[] = $mp;
                }

                $this->clean_match($match_id);

                [$match] = zone_util::draw_teams()->make_matches($draw_players);
                [$map_id, $match_civ_ids, $team1_civ_ids, $team2_civ_ids, $player_civ_ids] = zone_util::draw_settings()->draw_settings($match[0], $match[1]);
                $match_id = $this->create_match($remove_user_id, $match[0], $match[1], $map_id, $match_civ_ids, $team1_civ_ids, $team2_civ_ids, $player_civ_ids);
                if ($match_id) {
                    zone_util::players()->logout_players($player1_id, $player2_id);

                    return $match_id;
                }
            }

            return 0;
        });
    }

    public function clean_match(int $match_id): void
    {
        $team_ids = $this->get_match_team_ids($match_id);

        $where_match_id = ' WHERE `match_id` = ' . $match_id;
        $where_team_id = ' WHERE ' . $this->db->sql_in_set('team_id', $team_ids);
        $this->db->sql_query('DELETE FROM ' . $this->db->match_players_table . $where_team_id);
        $this->db->sql_query('DELETE FROM ' . $this->db->match_civs_table . $where_match_id);
        $this->db->sql_query('DELETE FROM ' . $this->db->match_team_civs_table . $where_team_id);
        $this->db->sql_query('DELETE FROM ' . $this->db->match_player_civs_table . $where_match_id);
        $this->db->sql_query('DELETE FROM ' . $this->db->match_teams_table . $where_team_id);
        $this->db->sql_query('DELETE FROM ' . $this->db->matches_table . $where_match_id);
    }

    /**
     * @param int $match_id
     * @param int $post_user_id
     * @param int $winner
     * @param int $topic_id
     * @throws \Throwable
     */
    public function post(int $match_id, int $post_user_id, int $winner, int $topic_id=0): void
    {
        $this->db->run_txn(function () use ($match_id, $post_user_id, $winner, $topic_id) {
            $already_posted = (int)$this->db->get_var([
                'SELECT' => 't.post_user_id',
                'FROM' => [$this->db->matches_table => 't'],
                'WHERE' => 't.match_id = ' . $match_id,
            ]);
            if ($already_posted) {
                return;
            }

            $end_time = time();

            [$team1_id, $team2_id] = $this->get_match_team_ids($match_id);

            if ($winner === 1 || $winner === 2) {
                $map_id = (int)$this->db->get_var([
                    'SELECT' => 't.map_id',
                    'FROM' => [$this->db->matches_table => 't'],
                    'WHERE' => 't.match_id = ' . $match_id
                ]);

                $winner_team_id = ($winner === 1) ? $team1_id : $team2_id;
                $team1_players = $this->get_teams_players($team1_id);
                $team2_players = $this->get_teams_players($team2_id);
                $match_players = $team1_players + $team2_players;
                $match_size = \count($team1_players);
                $match_points = $this->get_match_points($match_size);


                $match_civ_ids = $this->db->get_col([
                    'SELECT' => 't.civ_id',
                    'FROM' => [$this->db->match_civs_table => 't'],
                    'WHERE' => 't.match_id = ' . $match_id,
                ]);

                $team1_civ_ids = [];
                $team2_civ_ids = [];
                $team_civ_rows = $this->db->get_rows([
                    'SELECT' => 't.civ_id, t.team_id',
                    'FROM' => [$this->db->match_team_civs_table => 't'],
                    'WHERE' => 't.team_id = ' . $team1_id . ' OR t.team_id = ' . $team2_id,
                ]);
                foreach ($team_civ_rows as $r) {
                    if ($r['team_id'] == $team1_id) {
                        $team1_civ_ids[] = (int)$r['civ_id'];
                    } else {
                        $team2_civ_ids[] = (int)$r['civ_id'];
                    }
                }

                $player_civ_ids = [];
                $player_civ_rows = $this->db->get_rows([
                    'SELECT' => 't.civ_id, t.user_id',
                    'FROM' => [$this->db->match_player_civs_table => 't'],
                    'WHERE' => 't.match_id = ' . $match_id
                ]);
                foreach ($player_civ_rows as $r) {
                    $player_civ_ids[(int)$r['user_id']] = (int)$r['civ_id'];
                }

                $players = zone_util::players();
                $user1_ids = $user2_ids = [];
                foreach ($match_players as $user_id => $user_info) {
                    $team_id = (int)$user_info['team_id'];
                    $is_team1 = $team_id === $team1_id;
                    $has_won = $team_id === $winner_team_id;

                    if ($is_team1) {
                        $user1_ids[] = $user_id;
                    } else {
                        $user2_ids[] = $user_id;
                    }

                    $civ_ids = array_merge(
                        $match_civ_ids,
                        $is_team1 ? $team1_civ_ids : $team2_civ_ids,
                        array_key_exists($user_id, $player_civ_ids) ? [$player_civ_ids[$user_id]] : []
                    );

                    $this->db->update($this->db->player_civ_table, ['time' => $end_time], $this->db->sql_in_set('civ_id', $civ_ids) . ' AND `user_id` = ' . $user_id);

                    $players->match_changes($user_id, $team_id, $match_points, $has_won);
                    $players->fix_streaks($user_id, $match_id); // note: this isn't needed for normal game posting, but for fixing matches
                }
                $user_ids = array_merge($user1_ids, $user2_ids);

                $col1 = $winner === 1 ? 'matches_won' : 'matches_loss';
                $col2 = $winner === 2 ? 'matches_won' : 'matches_loss';
                $this->db->sql_query('UPDATE `' . $this->db->dreamteams_table . '` SET `' . $col1 . '` = `' . $col1 . '` + 1 WHERE `user1_id` < `user2_id` AND ' . $this->db->sql_in_set('user1_id', $user1_ids) . ' AND ' . $this->db->sql_in_set('user2_id', $user1_ids));
                $this->db->sql_query('UPDATE `' . $this->db->dreamteams_table . '` SET `' . $col2 . '` = `' . $col2 . '` + 1 WHERE `user1_id` < `user2_id` AND ' . $this->db->sql_in_set('user1_id', $user2_ids) . ' AND ' . $this->db->sql_in_set('user2_id', $user2_ids));

                $this->db->update($this->db->player_map_table, ['time' => $end_time], $this->db->sql_in_set('user_id', $user_ids) . ' AND `map_id` = ' . $map_id . ' AND `time` < ' . $end_time);

                $this->evaluate_bets($winner === 1 ? $team1_id : $team2_id, $winner === 1 ? $team2_id : $team1_id, $end_time);
            } else {
                $winner_team_id = 0;
            }

            // we don't want to create duplicate topics for a match
            if (!$topic_id && phpbb_util::config()['nczone_match_forum_id']) {
                $topic_id = $this->create_match_topic($match_id, $team1_id, $team2_id, $winner);
            }

            $this->db->update($this->db->matches_table, [
                'post_user_id' => $post_user_id,
                'post_time' => $end_time,
                'winner_team_id' => $winner_team_id,
                'forum_topic_id' => $topic_id,
            ], [
                'match_id' => $match_id
            ]);
        });
    }

    protected function create_match_topic(int $match_id, int $team1_id, int $team2_id, int $winner): int
    {
        $team_names = zone_util::players()->get_team_usernames($team1_id, $team2_id);
        $team1_str = $team2_str = '';
        if($winner === 1)
        {
            $team1_str = ' (W)';
            $team2_str = ' (L)';
        }
        elseif($winner === 2)
        {
            $team1_str = ' (L)';
            $team2_str = ' (W)';
        }

        $title = implode(', ', $team_names[$team1_id]) . $team1_str . ' vs. ' . implode(', ', $team_names[$team2_id]) . $team2_str;
        $message = '[match]' . $match_id . '[/match]';
        return zone_util::misc()->create_post($title, $message, phpbb_util::config()['nczone_match_forum_id']);
    }

    public function post_undo(int $match_id): void // todo: test this xD
    {
        $row = $this->db->get_row([
            'SELECT' => 't.post_time, t.winner_team_id',
            'FROM' => [$this->db->matches_table => 't'],
            'WHERE' => 't.match_id = ' . $match_id,
        ]);
        if(!$row)
        {
            return;
        }

        [$end_time, $winner_team_id] = [(int)$row['post_time'], (int)$row['winner_team_id']];
        [$team1_id, $team2_id] = $this->get_match_team_ids($match_id);
        $match_size = \count($this->get_teams_players($team1_id));
        $match_points = $this->get_match_points($match_size);

        foreach($this->get_teams_players($team1_id, $team2_id) as $user_id => $user_info)
        {
            $team_id = (int)$user_info['team_id'];
            zone_util::players()->match_changes_undo($user_id, $team_id, $match_points, $team_id === $winner_team_id);
        }

        $this->evaluate_bets_undo($winner_team_id == $team1_id ? $team1_id : $team2_id, $winner_team_id == $team2_id ? $team2_id : $team1_id, $end_time);

        $this->db->update($this->db->matches_table, [
            'post_user_id' => 0,
            'post_time' => 0,
            'winner_team_id' => 0,
        ], [
            'match_id' => $match_id
        ]);
    }

    public function get_match_players(int $match_id): array
    {
        return $this->get_teams_players(...$this->get_match_team_ids($match_id));
    }

    public function is_player_in_match(int $player_id, int $match_id): bool
    {
        return array_key_exists($player_id, $this->get_match_players($match_id));
    }

    public function get_match_team_ids(int $match_id): array
    {
        $col = $this->db->get_col([
            'SELECT' => 't.team_id',
            'FROM' => [$this->db->match_teams_table => 't'],
            'WHERE' => 't.match_id = ' . $match_id,
            'ORDER_BY' => 't.match_team ASC',
        ]);
        if(!$col)
        {
            return [];
        }

        return array_map(function($a) { return (int)$a; }, $col);
    }

    public function get_teams_players(int ...$team_ids): array
    {
        if(\count($team_ids) === 0) {
            return [];
        }

        $rows = $this->db->get_rows([
            'SELECT' => 't.team_id, t.user_id, t.draw_rating as rating',
            'FROM' => [$this->db->match_players_table => 't'],
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

        $users_right = $this->db->get_col([
            'SELECT' => 't.user_id',
            'FROM' => [$this->db->bets_table => 't'],
            'WHERE' => 't.team_id = '. $winner_team .' AND t.time <= ' . ($end_time - (int)$config['nczone_bet_time']),
        ]);
        $users_wrong = $this->db->get_col([
            'SELECT' => 't.user_id',
            'FROM' => [$this->db->bets_table => 't'],
            'WHERE' => 't.team_id = '. $loser_team .' AND t.time <= ' . ($end_time - (int)$config['nczone_bet_time']),
        ]);

        if($users_right)
        {
            $this->db->sql_query('UPDATE ' . $this->db->players_table . ' SET `bets_won` = `bets_won` + 1 WHERE ' . $this->db->sql_in_set('user_id', $users_right));
        }
        if($users_wrong)
        {
            $this->db->sql_query('UPDATE ' . $this->db->players_table . ' SET `bets_loss` = `bets_loss` + 1 WHERE ' . $this->db->sql_in_set('user_id', $users_wrong));
        }
    }

    public function evaluate_bets_undo(int $winner_team, int $loser_team, int $end_time): void
    {
        $config = phpbb_util::config();

        $users_right = $this->db->get_col([
            'SELECT' => 't.user_id',
            'FROM' => [$this->db->bets_table => 't'],
            'WHERE' => 't.team_id = '. $winner_team .' AND t.time <= ' . ($end_time - (int)$config['nczone_bet_time']),
        ]);
        $users_wrong = $this->db->get_col([
            'SELECT' => 't.user_id',
            'FROM' => [$this->db->bets_table => 't'],
            'WHERE' => 't.team_id = '. $loser_team .' AND t.time <= ' . ($end_time - (int)$config['nczone_bet_time']),
        ]);

        if($users_right)
        {
            $this->db->sql_query('UPDATE ' . $this->db->players_table . ' SET `bets_won` = `bets_won` - 1 WHERE ' . $this->db->sql_in_set('user_id', $users_right));
        }
        if($users_wrong)
        {
            $this->db->sql_query('UPDATE ' . $this->db->players_table . ' SET `bets_loss` = `bets_loss` - 1 WHERE ' . $this->db->sql_in_set('user_id', $users_wrong));
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
        $match_id = (int)$this->db->insert($this->db->matches_table, [
            'match_id' => 0,
            'map_id' => $map_id,
            'draw_user_id' => $draw_user_id,
            'post_user_id' => 0,
            'draw_time' => time(),
            'post_time' => 0,
            'winner_team_id' => 0,
            'forum_topic_id' => 0,
        ]);

        $team1_id = $this->insert_new_match_team($match_id, 1);
        $team2_id = $this->insert_new_match_team($match_id, 2);

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
            $this->db->sql_multi_insert($this->db->match_players_table, $team_data);
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
            $this->db->sql_multi_insert($this->db->match_civs_table, $match_civ_data);
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
            $this->db->sql_multi_insert($this->db->match_team_civs_table, $team_civ_data);
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
            $this->db->sql_multi_insert($this->db->match_player_civs_table, $player_civ_data);
        }

        return $match_id;
    }

    private function insert_new_match_team(int $match_id, int $match_team): int
    {
        return (int)$this->db->insert($this->db->match_teams_table, [
            'team_id' => 0,
            'match_id' => $match_id,
            'match_team' => $match_team,
        ]);
    }

    public function get_match(int $match_id): array
    {
        $sql = '
            SELECT 
                t.match_id, 
                t.map_id, 
                m.map_name, 
                t.draw_user_id, 
                t.post_user_id, 
                t.winner_team_id,
                u.username AS draw_username, 
                u2.username AS post_username, 
                t.draw_time,
                t.post_time
            FROM
                '.$this->db->matches_table.' t
                LEFT JOIN '.$this->db->maps_table.' m ON t.map_id = m.map_id
                LEFT JOIN '.$this->db->users_table.' u ON t.draw_user_id = u.user_id
                LEFT JOIN '.$this->db->users_table.' u2 ON t.draw_user_id = u2.user_id
            WHERE
                t.match_id = '.$match_id.'
            ;
        ';
        $m = $this->db->get_row($sql);
        if (!$m) {
            return null;
        }

        return $this->create_match_by_row($m);
    }

    public function get_all_rmatches(): array
    {
        $sql = '
            SELECT 
                t.match_id, 
                t.map_id, 
                m.map_name, 
                t.draw_user_id, 
                t.post_user_id, 
                u.username AS draw_username,  
                t.draw_time,
                t.post_time
            FROM
                '.$this->db->matches_table.' t
                LEFT JOIN '.$this->db->maps_table.' m ON t.map_id = m.map_id
                LEFT JOIN '.$this->db->users_table.' u ON t.draw_user_id = u.user_id
            WHERE
                t.post_time = 0
            ORDER BY
                t.draw_time DESC
            ;
        ';
        $match_rows = $this->db->get_rows($sql);
        $matches = [];
        foreach($match_rows as $m)
        {
            $matches[] = $this->create_match_by_row($m);
        }
        return $matches;
    }

    public function get_all_pmatches(): array
    {
        $sql = '
            SELECT 
                t.match_id, 
                t.map_id, 
                m.map_name, 
                t.draw_user_id, 
                t.post_user_id, 
                t.winner_team_id,
                u.username AS draw_username, 
                u2.username AS post_username, 
                t.draw_time,
                t.post_time
            FROM
                '.$this->db->matches_table.' t
                LEFT JOIN '.$this->db->maps_table.' m ON t.map_id = m.map_id
                LEFT JOIN '.$this->db->users_table.' u ON t.draw_user_id = u.user_id
                LEFT JOIN '.$this->db->users_table.' u2 ON t.draw_user_id = u2.user_id
            WHERE
                t.post_time > 0
            ORDER BY
                t.post_time DESC
            ;
        ';
        $match_rows = $this->db->get_rows($sql);
        $matches = [];
        foreach($match_rows as $m)
        {
            $matches[] = $this->create_match_by_row($m);
        }
        return $matches;
    }

    private function create_match_by_row(array $m): array
    {
        $match_id = (int)$m['match_id'];
        $map_id = (int)$m['map_id'];
        [$team1_id, $team2_id] = $this->get_match_team_ids($match_id);
        $winner_team_id = (int)($m['winner_team_id'] ?? 0);
        if ($winner_team_id === $team1_id) {
            $winner = 1;
        } elseif ($winner_team_id === $team2_id) {
            $winner = 2;
        } else {
            $winner = 0;
        }

        return [
            'id' => $match_id,
            'timestampStart' => (int)$m['draw_time'],
            'timestampEnd' => (int)($m['post_time'] ?? 0),
            'winner' => $winner,
            'whiner' => 'Snyper',
            'result_poster' => empty($m['post_user_id']) ? null : [
                'id' => (int)$m['post_user_id'],
                'name' => $m['post_username'],
            ],
            'drawer' => empty($m['draw_user_id']) ? null : [
                'id' => (int)$m['draw_user_id'],
                'name' => $m['draw_username'],
            ],
            'map' => empty($map_id) ? null : [
                'id' => $map_id,
                'title' => $m['map_name'],
            ],
            'civs' => array_merge(['both' => $this->get_match_civs($match_id, $map_id)], $this->get_team_civs($team1_id, $team2_id, $map_id)),
            'bets' => $this->get_bets($team1_id, $team2_id),
            'players' => zone_util::players()->get_match_players($match_id, $team1_id, $team2_id, $map_id),
        ];
    }

    public function get_match_civs(int $match_id, int $map_id): array
    {
        $rows = $this->db->get_rows([
            'SELECT' => 'm.civ_id AS id, c.civ_name AS title, mc.multiplier, m.number',
            'FROM' => [$this->db->match_civs_table => 'm', $this->db->civs_table => 'c', $this->db->map_civs_table => 'mc'],
            'WHERE' => 'm.civ_id = c.civ_id AND m.civ_id = mc.civ_id AND mc.map_id = ' . $map_id . ' AND m.match_id = ' . $match_id,
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

    public function get_team_civs(int $team1_id, int $team2_id, int $map_id): array
    {
        $rows = $this->db->get_rows([
            'SELECT' => 'm.team_id, m.civ_id AS id, c.civ_name AS title, mc.multiplier, m.number',
            'FROM' => [$this->db->match_team_civs_table => 'm', $this->db->civs_table => 'c', $this->db->map_civs_table => 'mc'],
            'WHERE' => 'm.civ_id = c.civ_id AND m.civ_id = mc.civ_id AND mc.map_id = ' . $map_id . ' AND (m.team_id = ' . $team1_id . ' OR m.team_id = ' . $team2_id . ')',
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

    public function is_over(int $match_id): bool
    {
        $sql = '
            SELECT 
                COUNT(*) 
            FROM 
                ' . $this->db->matches_table . ' 
            WHERE 
                match_id = ' . $match_id . ' 
                AND 
                post_time > 0
            ;
        ';
        return (int)$this->db->get_var($sql) > 0;
    }

    public function get_bets(int $team1_id, int $team2_id): array
    {
        $rows = $this->db->get_rows([
            'SELECT' => 'b.user_id, b.team_id, b.time, u.username',
            'FROM' => [$this->db->bets_table => 'b', $this->db->users_table => 'u'],
            'WHERE' => 'b.user_id = u.user_id AND (b.team_id = ' . $team1_id . ' OR b.team_id = ' . $team2_id . ')'
        ]);

        $bets = [
            'team1' => [],
            'team2' => [],
        ];
        foreach($rows as $r)
        {
            $teamIndex = (int)$r['team_id'] === $team1_id ? 'team1' : 'team2';
            $bets[$teamIndex][] = [
                'timestamp' => (int)$r['time'],
                'user' => [
                    'id' => (int)$r['user_id'],
                    'name' => $r['username'],
                ],
            ];
        }

        return $bets;
    }

    private function check_draw_process(int $user_id=0): int
    {
        $row = $this->db->get_row([
            'SELECT' => 't.draw_id, t.time',
            'FROM' => [$this->db->draw_process_table => 't'],
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
            $this->db->sql_query('TRUNCATE `' . $this->db->draw_process_table . '`');
            $this->db->sql_query('TRUNCATE `' . $this->db->draw_players_table . '`');
            return 0;
        }

        return $draw_id;
    }

    private function get_draw_players(int $draw_id): array
    {
        $rows = $this->db->get_rows([
            'SELECT' => 'p.user_id AS id, u.username, p.rating, d.logged_in',
            'FROM' => [$this->db->draw_players_table => 'd', $this->db->players_table => 'p', $this->db->users_table => 'u'],
            'WHERE' => 'd.draw_id = ' . $draw_id . ' AND d.user_id = p.user_id AND p.user_id = u.user_id',
            'ORDER_BY' => 'd.logged_in ASC'
        ]);
        return array_map(function($row) {
            return [
                'id' => (int)$row['id'],
                'username' => $row['username'], // todo: probably not needed
                'rating' => (int)$row['rating'],
                'logged_in' => (int)$row['logged_in'],
            ];
        }, $rows);
    }

    /**
     * @param int $user_id
     * @return array
     * @throws \Throwable
     */
    public function start_draw_process(int $user_id): array
    {
        return $this->db->run_txn(function () use ($user_id) {
            if ($this->check_draw_process()) {
                return [];
            }

            $logged_in = zone_util::players()->get_logged_in();
            if (\count($logged_in) < 2) {
                return [];
            }

            $draw_id = (int)$this->db->insert($this->db->draw_process_table, [
                'draw_id' => 0,
                'user_id' => $user_id,
                'time' => time(),
            ]);

            $sql_array = [];
            foreach ($logged_in as $u) {
                $sql_array[] = [
                    'draw_id' => $draw_id,
                    'user_id' => $u['id'],
                    'logged_in' => $u['logged_in'],
                ];
            }
            $this->db->sql_multi_insert($this->db->draw_players_table, $sql_array);
            return $logged_in;
        });
    }

    /**
     * @param int $user_id
     * @return array
     * @throws \Throwable
     */
    public function confirm_draw_process(int $user_id): array
    {
        return $this->db->run_txn(function () use ($user_id) {
            $draw_id = $this->check_draw_process($user_id);
            if (!$draw_id) {
                return [];
            }
            $draw_players = $this->get_draw_players($draw_id);
            $this->db->sql_query('TRUNCATE `' . $this->db->draw_process_table . '`');
            $this->db->sql_query('TRUNCATE `' . $this->db->draw_players_table . '`');
            return $draw_players;
        });
    }

    /**
     * @param int $user_id
     * @throws \Throwable
     */
    public function deny_draw_process(int $user_id): void
    {
        $this->db->run_txn(function () use ($user_id) {
            if ($this->check_draw_process($user_id)) {
                $this->db->sql_query('TRUNCATE `' . $this->db->draw_process_table . '`');
                $this->db->sql_query('TRUNCATE `' . $this->db->draw_players_table . '`');
            }
        });
    }
}
