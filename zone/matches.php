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
use eru\nczone\utility\zone_util;
use eru\nczone\zone\entity\match;
use eru\nczone\zone\error\InvalidDrawIdError;
use eru\nczone\zone\error\NoDrawPlayersError;

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

    private static function get_draw_factor()
    {
        return config::get(config::draw_factor);
    }

    /**
     * @param int $user_id
     * @param int $match_id
     * @param int $replace_player_id
     * @return array
     * @throws \Throwable
     */
    public function replace_player(int $user_id, int $match_id, int $replace_player_id): array
    {
        return $this->db->run_txn(function () use ($user_id, $match_id, $replace_player_id) {
            $draw_id = $this->get_draw_id_by_user_id($user_id);
            if (!$draw_id) {
                throw new InvalidDrawIdError();
            }
            $players_list = $this->get_draw_players($draw_id);
            if ($players_list->length() === 0) {
                throw new NoDrawPlayersError();
            }
            if($this->is_over($match_id)) {
                return []; // todo: throw new
            }

            $this->clear_draw_tables();

            if ($players_list->length() < 1) {
                return [];
            }

            $player2_id = $players_list->get_ids()[0];

            $match_players = $this->get_match_players($match_id);

            if (!$match_players->contains_id($replace_player_id) || $match_players->contains_id($player2_id)) {
                return [];
            }

            $match_players->remove_by_id($replace_player_id);

            $p = zone_util::players()->get_player($player2_id);

            $match_players->unshift(entity\match_player::create_by_player($p));

            $this->clear_draw_tables();

            $this->clean_match($match_id);

            $factor = self::get_draw_factor();
            $draw_match = zone_util::draw_teams()->make_match($match_players, $factor);
            $setting = zone_util::draw_settings()->draw_settings($user_id, $draw_match);
            $match_id = $this->create_match($setting);
            if ($match_id) {
                zone_util::players()->logout_player($player2_id);
                return ['match_id' => $match_id];
            }
            return [];
        });
    }

    /**
     * @param int $user_id
     * @param int $match_id
     * @return array
     * @throws \Throwable
     */
    public function add_pair(int $user_id, int $match_id): array
    {
        return $this->db->run_txn(function () use ($user_id, $match_id) {
            $draw_id = $this->get_draw_id_by_user_id($user_id);
            if (!$draw_id) {
                throw new InvalidDrawIdError();
            }

            $players_list = $this->get_draw_players($draw_id);
            if ($players_list->length() < 2) {
                throw new NoDrawPlayersError();
            }
            if($this->is_over($match_id)) {
                return []; // todo: throw new
            }

            /** @var entity\match_player $player1 */
            $player1 = $players_list->shift();
            /** @var entity\match_player $player2 */
            $player2 = $players_list->shift();

            $match_players = $this->get_match_players($match_id);
            if (
                $match_players->length() >= 8
                || $match_players->contains_id($player1->get_id())
                || $match_players->contains_id($player2->get_id())
            ) {
                return [];
            }

            $match_players->unshift($player1);
            $match_players->unshift($player2);

            $this->clear_draw_tables();

            $this->clean_match($match_id);

            $factor = self::get_draw_factor();
            $draw_match = zone_util::draw_teams()->make_match($match_players, $factor);
            $setting = zone_util::draw_settings()->draw_settings($user_id, $draw_match);
            $match_id = $this->create_match($setting);
            if ($match_id) {
                zone_util::players()->logout_player($player1->get_id());
                zone_util::players()->logout_player($player2->get_id());
                return ['match_id' => $match_id];
            }
            return [];
        });
    }

    /**
     * @param int $user_id
     * @param int $match_id
     * @param int $player1_id
     * @param int $player2_id
     * @return int
     * @throws \Throwable
     */
    public function remove_pair(int $user_id, int $match_id, int $player1_id, int $player2_id): int
    {
        return $this->db->run_txn(function () use ($user_id, $match_id, $player1_id, $player2_id) {
            $match_players = $this->get_match_players($match_id);
            if (!$match_players->contains_id($player1_id) || !$match_players->contains_id($player2_id) || $match_players->length() <= 2) {
                return 0;
            }

            $match_players->remove_by_id($player1_id);
            $match_players->remove_by_id($player2_id);

            $this->clean_match($match_id);

            $factor = self::get_draw_factor();
            $draw_match = zone_util::draw_teams()->make_match($match_players, $factor);
            $setting = zone_util::draw_settings()->draw_settings($user_id, $draw_match);
            $match_id = $this->create_match($setting);
            if ($match_id) {
                zone_util::players()->logout_players($player1_id, $player2_id);

                return $match_id;
            }
            return 0;
        });
    }

    /**
     * Note: this is always run in a transaction, no need to wrap its calls
     * @param int $match_id
     */
    private function clean_match(int $match_id): void
    {
        $team_ids = $this->get_match_team_ids($match_id);
        $where_match_id = "`match_id` = {$match_id}";
        $where_team_id = $this->db->sql_in_set('team_id', $team_ids);

        $this->db->sql_query("DELETE FROM {$this->db->match_players_table} WHERE {$where_team_id};");
        $this->db->sql_query("DELETE FROM {$this->db->match_civs_table} WHERE {$where_match_id};");
        $this->db->sql_query("DELETE FROM {$this->db->match_team_civs_table} WHERE {$where_team_id};");
        $this->db->sql_query("DELETE FROM {$this->db->match_player_civs_table} WHERE {$where_match_id};");
        $this->db->sql_query("DELETE FROM {$this->db->match_teams_table} WHERE {$where_team_id};");
        $this->db->sql_query("DELETE FROM {$this->db->matches_table} WHERE {$where_match_id};");
    }

    /**
     * @param int $match_id
     * @param int $post_user_id
     * @param int $winner
     * @param bool $repost
     * @throws \Throwable
     */
    public function post(int $match_id, int $post_user_id, int $winner, bool $repost=false): void
    {
        $this->db->run_txn(function () use ($match_id, $post_user_id, $winner, $repost) {
            $row = $this->db->get_row('
                SELECT
                    t.map_id,
                    t.draw_time,
                    t.post_time,
                    t.post_user_id,
                    t1.team_id AS team1_id,
                    t2.team_id AS team2_id
                FROM 
                    ' . $this->db->matches_table . ' t
                    INNER JOIN '.$this->db->match_teams_table.' t1 ON t1.match_id = t.match_id AND t1.match_team = 1
                    INNER JOIN '.$this->db->match_teams_table.' t2 ON t2.match_id = t.match_id AND t2.match_team = 2
                WHERE
                    t.match_id = ' . $match_id . ' 
                ;
            ');
            $already_posted = (int)$row['post_user_id'];
            if ($already_posted && !$repost) {
                return;
            }
            if(!$post_user_id) {
                $post_user_id = (int)$row['post_user_id'];
            }

            $players = zone_util::players();
            $draw_time = (int)$row['draw_time'];
            $end_time = $repost ? (int)$row['post_time'] : time();
            $team1_id = (int)$row['team1_id'];
            $team2_id = (int)$row['team2_id'];

            if ($winner === 1 || $winner === 2) {
                $map_id = (int)$row['map_id'];

                $winner_team_id = ($winner === 1) ? $team1_id : $team2_id;
                $team1_list = $this->get_teams_players($team1_id);
                $team2_list = $this->get_teams_players($team2_id);

                $match_points = $this->get_match_points_by_match_size(
                    $team1_list->length(),
                    $team1_list->get_rating_difference($team2_list),
                    $winner
                );


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

                $teams = [
                    $team1_list,
                    $team2_list
                ];
                /**
                 * @var int $idx
                 * @var entity\match_players_list $team_list
                 */
                foreach ($teams as $idx => $team_list) {
                    $is_team1 = $idx === 0;
                    $team_id = $is_team1 ? $team1_id : $team2_id;
                    $team_civ_ids = $is_team1 ? $team1_civ_ids : $team2_civ_ids;
                    foreach ($team_list->items() as $mp) {
                        $civ_ids = array_merge(
                            $match_civ_ids,
                            $team_civ_ids,
                            array_key_exists($mp->get_id(), $player_civ_ids) ? [$player_civ_ids[$mp->get_id()]] : []
                        );
                        if (!empty($civ_ids)) {
                            $this->db->update($this->db->player_civ_table, ['time' => $draw_time], $this->db->sql_in_set('civ_id', $civ_ids) . ' AND `user_id` = ' . $mp->get_id() . ' AND `time` < ' . $draw_time);
                        }
                        $players->match_changes($mp->get_id(), $team_id, $match_points, $team_id === $winner_team_id);
                        $players->fix_streaks($mp->get_id(), $match_id); // note: this isn't needed for normal game posting, but for fixing matches
                    }
                }

                $user1_ids = $team1_list->get_ids();
                $user2_ids = $team2_list->get_ids();

                $col1 = $winner === 1 ? 'matches_won' : 'matches_loss';
                $col2 = $winner === 2 ? 'matches_won' : 'matches_loss';
                $this->db->sql_query('UPDATE `' . $this->db->dreamteams_table . '` SET `' . $col1 . '` = `' . $col1 . '` + 1 WHERE `user1_id` < `user2_id` AND ' . $this->db->sql_in_set('user1_id', $user1_ids) . ' AND ' . $this->db->sql_in_set('user2_id', $user1_ids));
                $this->db->sql_query('UPDATE `' . $this->db->dreamteams_table . '` SET `' . $col2 . '` = `' . $col2 . '` + 1 WHERE `user1_id` < `user2_id` AND ' . $this->db->sql_in_set('user1_id', $user2_ids) . ' AND ' . $this->db->sql_in_set('user2_id', $user2_ids));

                $user_ids = array_merge($user1_ids, $user2_ids);
                $this->db->update($this->db->player_map_table, ['time' => $draw_time], $this->db->sql_in_set('user_id', $user_ids) . ' AND `map_id` = ' . $map_id . ' AND `time` < ' . $draw_time);

                zone_util::bets()->evaluate_bets($winner === 1 ? $team1_id : $team2_id, $winner === 1 ? $team2_id : $team1_id, $end_time);
            } else {
                foreach($this->get_teams_players($team1_id, $team2_id)->items() as $mp) {
                    $players->fix_streaks($mp->get_id(), $match_id); // note: this isn't needed for normal game posting, but for fixing matches
                }

                $winner_team_id = 0;
            }

            $update_sql = [
                'post_user_id' => $post_user_id,
                'post_time' => $end_time,
                'winner_team_id' => $winner_team_id,
            ];

            // we don't want to create duplicate topics for a match
            $forum_id = config::get(config::match_forum_id);
            if (!$repost && $forum_id) {
                $update_sql['forum_topic_id'] = $this->create_match_topic($match_id, $team1_id, $team2_id, $winner, $forum_id);
            }

            $this->db->update($this->db->matches_table, $update_sql, [
                'match_id' => $match_id
            ]);
        });
    }

    protected function create_match_topic(int $match_id, int $team1_id, int $team2_id, int $winner, int $forum_id): int
    {
        $team_names = zone_util::players()->get_team_usernames($team1_id, $team2_id);
        if ($winner === 1) {
            $team1_str = ' (W)';
            $team2_str = ' (L)';
        } elseif ($winner === 2) {
            $team1_str = ' (L)';
            $team2_str = ' (W)';
        } else {
            $team1_str = '';
            $team2_str = '';
        }

        $title = '[#' . $match_id . '] '
            . implode(', ', $team_names[$team1_id]) . $team1_str
            . ' vs. '
            . implode(', ', $team_names[$team2_id]) . $team2_str;
        $message = '[match]' . $match_id . '[/match]';
        return zone_util::misc()->create_post($title, $message, $forum_id);
    }

    public function post_undo(int $match_id): void // todo: test this xD // todo: dreamteams
    {
        $row = $this->db->get_row([
            'SELECT' => 't.post_time, t.winner_team_id',
            'FROM' => [$this->db->matches_table => 't'],
            'WHERE' => 't.match_id = ' . $match_id,
        ]);
        if (!$row) {
            return;
        }
        if (!$row['winner_team_id']) {
            return;
        }

        [$end_time, $winner_team_id] = [(int)$row['post_time'], (int)$row['winner_team_id']];
        [$team1_id, $team2_id] = $this->get_match_team_ids($match_id);
        $team1_list = $this->get_teams_players($team1_id);
        $team2_list = $this->get_teams_players($team2_id);

        $match_points = $this->get_match_points_by_match_size(
            $team1_list->length(),
            $team1_list->get_rating_difference($team2_list),
            $winner_team_id == $team1_id ? 1 : 2
        );

        foreach ($team1_list->items() as $mp) {
            zone_util::players()->match_changes_undo(
                $mp->get_id(),
                $team1_id,
                $match_points,
                $team1_id === $winner_team_id
            );
        }
        foreach ($team2_list->items() as $mp) {
            zone_util::players()->match_changes_undo(
                $mp->get_id(),
                $team2_id,
                $match_points,
                $team2_id === $winner_team_id
            );
        }

        $user1_ids = $team1_list->get_ids();
        $col1 = $winner_team_id == $team1_id ? 'matches_won' : 'matches_loss';
        $this->db->sql_query('UPDATE `' . $this->db->dreamteams_table . '` SET `' . $col1 . '` = `' . $col1 . '` - 1 WHERE `user1_id` < `user2_id` AND ' . $this->db->sql_in_set('user1_id', $user1_ids) . ' AND ' . $this->db->sql_in_set('user2_id', $user1_ids));

        $user2_ids = $team2_list->get_ids();
        $col2 = $winner_team_id == $team2_id ? 'matches_won' : 'matches_loss';
        $this->db->sql_query('UPDATE `' . $this->db->dreamteams_table . '` SET `' . $col2 . '` = `' . $col2 . '` - 1 WHERE `user1_id` < `user2_id` AND ' . $this->db->sql_in_set('user1_id', $user2_ids) . ' AND ' . $this->db->sql_in_set('user2_id', $user2_ids));

        zone_util::bets()->evaluate_bets_undo(
            $winner_team_id == $team1_id ? $team1_id : $team2_id,
            $winner_team_id == $team1_id ? $team2_id : $team1_id,
            $end_time
        );

        $this->db->update($this->db->matches_table, [
            'winner_team_id' => 0,
        ], [
            'match_id' => $match_id
        ]);
    }

    public function get_match_players(int $match_id): entity\match_players_list
    {
        return $this->get_teams_players(...$this->get_match_team_ids($match_id));
    }

    public function is_player_in_match(int $player_id, int $match_id): bool
    {
        return $this->get_match_players($match_id)->contains_id($player_id);
    }

    public function get_match_team_ids(int $match_id): array
    {
        $col = $this->db->get_col([
            'SELECT' => 't.team_id',
            'FROM' => [$this->db->match_teams_table => 't'],
            'WHERE' => 't.match_id = ' . $match_id,
            'ORDER_BY' => 't.match_team ASC',
        ]);
        return array_map('\intval', $col);
    }

    public function get_teams_players(int ...$team_ids): entity\match_players_list
    {
        if(\count($team_ids) === 0) {
            return new entity\match_players_list;
        }

        $rows = $this->db->get_rows([
            'SELECT' => 't.user_id AS id, t.draw_rating as rating',
            'FROM' => [$this->db->match_players_table => 't'],
            'WHERE' => $this->db->sql_in_set('t.team_id', $team_ids)
        ]);

        $list = new entity\match_players_list;
        foreach ($rows as $r) {
            $list->add(entity\match_player::create_by_row($r));
        }
        return $list;
    }

    private function get_match_points_by_match_size(
        int $match_size,
        int $rating_diff,
        int $winner
    ): int {
        $base_points = config::base_points_by_match_size($match_size);
        if ($base_points < 0) {
            return 0;
        }

        $is_outsider_win = ($rating_diff > 0 xor $winner === 1) ? true : false;
        $factor = $is_outsider_win ? 1 : -1;
        $extra_points = (int)floor(abs($rating_diff) / (float)config::get(config::extra_points));
        return \max(0, $base_points + $factor * $extra_points);
    }

    /**
     * Creates a match and returns the match id
     *
     * @param entity\draw_setting $setting   ID of the map to be played
     *
     * @return int
     */
    public function create_match(entity\draw_setting $setting): int
    {
        $match_id = $this->db->insert($this->db->matches_table, [
            'match_id' => 0,
            'map_id' => $setting->get_map_id(),
            'draw_user_id' => $setting->get_draw_user_id(),
            'post_user_id' => 0,
            'draw_time' => time(),
            'post_time' => 0,
            'winner_team_id' => 0,
            'forum_topic_id' => 0,
        ]);

        $team1_id = $this->insert_new_match_team($match_id, 1);
        $team2_id = $this->insert_new_match_team($match_id, 2);

        $team_data = [];
        foreach($setting->get_team1()->items() as $player)
        {
            $team_data[] = [
                'team_id' => $team1_id,
                'user_id' => $player->get_id(),
                'draw_rating' => $player->get_rating(),
                'rating_change' => 0,
            ];
        }
        foreach($setting->get_team2()->items() as $player)
        {
            $team_data[] = [
                'team_id' => $team2_id,
                'user_id' => $player->get_id(),
                'draw_rating' => $player->get_rating(),
                'rating_change' => 0,
            ];
        }
        if(!empty($team_data))
        {
            $this->db->sql_multi_insert($this->db->match_players_table, $team_data);
        }

        $match_civ_data = [];
        $match_civ_ids = $setting->get_match_civ_ids();
        $match_civ_numbers = array_count_values($match_civ_ids);
        foreach(array_unique($match_civ_ids) as $civ_id)
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
        $team1_civ_ids = $setting->get_team1_civ_ids();
        $team1_civ_numbers = array_count_values($team1_civ_ids);
        foreach(array_unique($team1_civ_ids) as $civ_id)
        {
            $team_civ_data[] = [
                'team_id' => $team1_id,
                'civ_id' => (int)$civ_id,
                'number' => $team1_civ_numbers[$civ_id],
            ];
        }
        $team2_civ_ids = $setting->get_team2_civ_ids();
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
        foreach($setting->get_player_civ_ids() as $user_id => $civ_id)
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
        return $this->db->insert($this->db->match_teams_table, [
            'team_id' => 0,
            'match_id' => $match_id,
            'match_team' => $match_team,
        ]);
    }

    private function load_match_rows(
        array $where = [],
        array $order = [],
        int $offset = 0,
        int $limit = 0
    ): array {
        $whereSql = empty($where) ? '' : ' WHERE ' . \implode(' AND ', $where);
        $orderBySql = empty($order) ? '' : ' ORDER BY ' . \implode(',', $order);
        $sql = <<<SQL
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
    t.post_time,
    t1.team_id AS team1_id,
    t2.team_id AS team2_id,
    t.forum_topic_id
FROM
  {$this->db->matches_table} AS t
  INNER JOIN {$this->db->match_teams_table} AS t1
  ON t1.match_id = t.match_id AND t1.match_team = 1
  INNER JOIN {$this->db->match_teams_table} AS t2
  ON t2.match_id = t.match_id AND t2.match_team = 2
  LEFT JOIN {$this->db->maps_table} AS m
  ON t.map_id = m.map_id
  LEFT JOIN {$this->db->users_table} AS u
  ON t.draw_user_id = u.user_id
  LEFT JOIN {$this->db->users_table} AS u2
  ON t.post_user_id = u2.user_id
{$whereSql}
{$orderBySql}
SQL;
        return $this->db->get_rows($sql, $limit, $offset);
    }

    public function get_match(int $match_id): entity\match
    {
        $rows = $this->load_match_rows(["t.match_id = {$match_id}"]);
        if (empty($rows)) {
            return null;
        }
        return entity\match::create_by_row_finished($rows[0]);
    }

    public function get_all_rmatches(): array
    {
        $rows = $this->load_match_rows(['t.post_time = 0'], ['t.draw_time DESC']);
        return \array_map([match::class, 'create_by_row_unfinished'], $rows);
    }

    public function get_pmatches(int $page = 0): array
    {
        $limit = (int)config::get(config::pmatches_page_size);
        $offset = $page * $limit;
        $rows = $this->load_match_rows(['t.post_time > 0'], ['t.post_time DESC'], $offset, $limit);
        return \array_map([entity\match::class, 'create_by_row_finished'], $rows);
    }

    public function get_pmatches_total_pages(): int
    {
        $num_matches = (int)$this->db->get_var('SELECT COUNT(*) FROM ' . $this->db->matches_table . ' WHERE post_time > 0');
        $page_size = (int)config::get(config::pmatches_page_size);
        return ceil($num_matches / $page_size);
    }

    public function get_match_civs(int $match_id, int $map_id): array
    {
        $rows = $this->db->get_rows([
            'SELECT' => 'm.civ_id AS id, c.civ_name AS title, mc.multiplier, m.number',
            'FROM' => [$this->db->match_civs_table => 'm', $this->db->civs_table => 'c', $this->db->map_civs_table => 'mc'],
            'WHERE' => 'm.civ_id = c.civ_id AND m.civ_id = mc.civ_id AND mc.map_id = ' . $map_id . ' AND m.match_id = ' . $match_id,
            'ORDER_BY' => 'mc.multiplier DESC'
        ]);

        return \array_map(function($row) {
            return [
                'id' => (int)$row['id'],
                'title' => $row['title'],
                'number' => (int)$row['number'],
                'multiplier' => (float)$row['multiplier'],
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
            $r['multiplier'] = (int)$r['multiplier'];

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

    public function get_winner(int $match_id): int
    {
        $sql = 'SELECT
                    t.match_team
                FROM
                    ' . $this->db->match_teams_table . ' t
                    LEFT JOIN ' . $this->db->matches_table . ' m ON t.team_id = m.winner_team_id
                WHERE
                    m.match_id = ' . $match_id;
        return (int)$this->db->get_var($sql);
    }

    public function is_any_draw_process_active(): bool
    {
        return $this->get_draw_id_by_user_id() > 0;
    }

    public function get_draw_id_by_user_id(int $user_id = 0): int
    {
        $row = $this->db->get_row([
            'SELECT' => 't.draw_id, t.time',
            'FROM' => [$this->db->draw_process_table => 't'],
            'WHERE' => $user_id ? ('t.user_id = ' . $user_id) : '1',
        ]);
        if (!$row) {
            return 0;
        }

        $draw_process_time = (int)$row['time'];
        if ($draw_process_time && ((time() - $draw_process_time) > config::get(config::draw_time))) {
            $this->clear_draw_tables();
            return 0;
        }

        return (int)$row['draw_id'];
    }

    public function get_draw_players(int $draw_id): entity\match_players_list
    {
        $rows = $this->db->get_rows([
            'SELECT' => 'p.user_id AS id, p.rating',
            'FROM' => [$this->db->draw_players_table => 'd', $this->db->players_table => 'p'],
            'WHERE' => 'd.draw_id = ' . $draw_id . ' AND d.user_id = p.user_id',
            'ORDER_BY' => 'd.logged_in ASC'
        ]);
        $list = new entity\match_players_list;
        foreach ($rows as $row) {
            $list->add(entity\match_player::create_by_row($row));
        }
        return $list;
    }

    /**
     * @param int $user_id
     * @return entity\player[] Array of logged in players
     * @throws \Throwable
     */
    public function start_draw_process(int $user_id): array
    {
        return $this->db->run_txn(function () use ($user_id) {
            if ($this->is_any_draw_process_active()) {
                return [];
            }

            $players = zone_util::players()->get_logged_in();
            if (\count($players) < 1) {
                return [];
            }

            $draw_id = $this->insert_draw_process($user_id);
            $this->insert_draw_process_players($draw_id, $players);
            return $players;
        });
    }

    private function insert_draw_process(int $user_id): int
    {
        return $this->db->insert($this->db->draw_process_table, [
            'draw_id' => 0,
            'user_id' => $user_id,
            'time' => time(),
        ]);
    }

    private function insert_draw_process_players(int $draw_id, array $players): void
    {
        $this->db->sql_multi_insert(
            $this->db->draw_players_table,
            \array_map(function (entity\player $player) use ($draw_id) {
                return [
                    'draw_id' => $draw_id,
                    'user_id' => $player->get_id(),
                    'logged_in' => $player->get_logged_in(),
                ];
            }, $players)
        );
    }

    /**
     * @param int $user_id
     * @throws \Throwable
     */
    public function deny_draw_process(int $user_id): void
    {
        $this->db->run_txn(function () use ($user_id) {
            if ($this->get_draw_id_by_user_id($user_id)) {
                $this->clear_draw_tables();
            }
        });
    }

    private function clear_draw_tables(): void
    {
        $this->db->sql_query('TRUNCATE `' . $this->db->draw_process_table . '`');
        $this->db->sql_query('TRUNCATE `' . $this->db->draw_players_table . '`');
    }

    /**
     * @param int $user_id
     * @return int[] Match ids created by draw process
     * @throws \Throwable
     */
    public function draw(int $user_id): array
    {
        return $this->db->run_txn(function () use ($user_id) {
            $draw_id = $this->get_draw_id_by_user_id($user_id);
            if (!$draw_id) {
                throw new InvalidDrawIdError();
            }
            $players_list = $this->get_draw_players($draw_id);
            if ($players_list->length() === 0) {
                throw new NoDrawPlayersError();
            }

            $this->clear_draw_tables();

            if ($players_list->length() < 2) {
                return [];
            }

            $match_ids = [];
            $user_ids = [];
            $factor = self::get_draw_factor();
            $draw_matches = zone_util::draw_teams()->make_matches($players_list, $factor);
            foreach ($draw_matches as $draw_match) {
                $setting = zone_util::draw_settings()->draw_settings($user_id, $draw_match);
                $match_ids[] = $this->create_match($setting);

                foreach ($draw_match->get_all_player_ids() as $player_id) {
                    $user_ids[] = $player_id;
                }
            }
            zone_util::players()->logout_players(...$user_ids);
            return $match_ids;
        });
    }

//TODO eru: Does it work like that???
    public function get_drawer_id(int $match_id): int
    {
        //TODO!
        $sql = 'SELECT draw_user_id FROM '
                . $this->db->matches_table . 
                ' WHERE '
                    .'match_id = ' . $match_id ;
         return (int)$this->db->get_var($sql);
    }
}
