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
use eru\nczone\config\acl;
use eru\nczone\utility\zone_util;
use eru\nczone\zone\error\InvalidDrawIdError;
use eru\nczone\zone\error\NoDrawPlayersError;

/**
 * nC Zone matches management class.
 */
class matches {

    /** @var db */
    private $db;

    /** @var acl */
    private $acl;

    /** @var config */
    private $config;

    /** @var players */
    private $players;

    /** @var draw_teams */
    private $draw_teams;

    /** @var draw_settings */
    private $draw_settings;

    /** @var bets */
    private $bets;

    /** @var locks */
    private $locks;

    /** @var misc */
    private $misc;

    /** @var maps */
    private $maps;

    /**
     * Constructor
     *
     * @param db $db Database object
     */
    public function __construct(db $db, acl $acl)
    {
        $this->db = $db;
        $this->acl = $acl;
        // TODO: provide via dependency injection
        $this->config = zone_util::config();
        $this->players = zone_util::players();
        $this->draw_teams = zone_util::draw_teams();
        $this->draw_settings = zone_util::draw_settings();
        $this->bets = zone_util::bets();
        $this->locks = zone_util::locks();
        $this->misc = zone_util::misc();
        $this->maps = zone_util::maps();
    }

    private function get_draw_factor()
    {
        return $this->config->get(config::draw_factor);
    }

    private function get_draw_switch_player_numbers(): array {
        return [
            0 => (int) $this->config->get(config::draw_switch_0_players),
            1 => (int) $this->config->get(config::draw_switch_1_player)
        ];
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

            $p = $this->players->get_player($player2_id);

            $match_players->unshift(entity\match_player::create_by_player($p));

            $this->clear_draw_tables();

            $this->clean_match($match_id);

            $factor = $this->get_draw_factor();
            $draw_match = $this->draw_teams->make_match($match_players, $factor);
            $setting = $this->draw_settings->draw_settings($user_id, $draw_match);
            $match_id = $this->create_match($setting);
            if ($match_id) {
                $this->players->logout_players($player2_id);
                return ['match_id' => $match_id, 'replace_player_id' => $replace_player_id, 'new_player_id' => $player2_id];
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

            $factor = $this->get_draw_factor();
            $draw_match = $this->draw_teams->make_match($match_players, $factor);
            $setting = $this->draw_settings->draw_settings($user_id, $draw_match);
            $match_id = $this->create_match($setting);
            if ($match_id) {
                $player1_id = $player1->get_id();
                $player2_id = $player2->get_id();

                $this->players->logout_players($player1_id, $player2_id);
                return ['match_id' => $match_id, 'player1_id' => $player1_id, 'player2_id' => $player2_id];
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

            $factor = $this->get_draw_factor();
            $draw_match = $this->draw_teams->make_match($match_players, $factor);
            $setting = $this->draw_settings->draw_settings($user_id, $draw_match);
            $match_id = $this->create_match($setting);
            if ($match_id) {
                $this->players->logout_players($player1_id, $player2_id);

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

        $this->db->delete($this->db->match_players_table, ['team_id' => ['$in' => $team_ids]]);
        $this->db->delete($this->db->match_civs_table, ['match_id' => $match_id]);
        $this->db->delete($this->db->match_team_civs_table, ['team_id' => ['$in' => $team_ids]]);
        $this->db->delete($this->db->match_player_civs_table, ['match_id' => $match_id]);
        $this->db->delete($this->db->match_teams_table, ['team_id' => ['$in' => $team_ids]]);
        $this->db->delete($this->db->matches_table, ['match_id' => $match_id]);
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
        $fn = function () use ($match_id, $post_user_id, $winner, $repost) {
            $row = $this->db->get_row('
                SELECT
                    t.map_id,
                    t.draw_time,
                    t.post_time,
                    t.post_user_id,
                    t.forum_topic_id,
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

            $draw_time = (int)$row['draw_time'];
            $end_time = $repost ? (int)$row['post_time'] : time();
            $team1_id = (int)$row['team1_id'];
            $team2_id = (int)$row['team2_id'];

            $min_team_id = (int) \min($team1_id, $team2_id);

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
                $match_points_team1 = $match_points[1];
                $match_points_team2 = $match_points[2];

                $match_civ_ids = $this->get_civ_ids_by_match($match_id);

                $team1_civ_ids = [];
                $team2_civ_ids = [];
                $team_civ_rows = $this->db->get_rows([
                    'SELECT' => 'civ_id, team_id',
                    'FROM' => [$this->db->match_team_civs_table => 't'],
                    'WHERE' => ['team_id' => ['$in' => [$team1_id, $team2_id]]],
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
                        $this->players->match_changes($mp->get_id(), $team_id, $is_team1 ? $match_points_team1 : $match_points_team2, $team_id === $winner_team_id);

                        $this->players->fix_streaks($mp->get_id(), $min_team_id); // note: this isn't needed for normal game posting, but for fixing matches
                    }
                }

                $user1_ids = $team1_list->get_ids();
                $user2_ids = $team2_list->get_ids();

                $col1 = $winner === 1 ? 'matches_won' : 'matches_loss';
                $col2 = $winner === 2 ? 'matches_won' : 'matches_loss';
                $this->db->sql_query('UPDATE `' . $this->db->dreamteams_table . '` SET `' . $col1 . '` = `' . $col1 . '` + 1 WHERE `user1_id` < `user2_id` AND ' . $this->db->sql_in_set('user1_id', $user1_ids) . ' AND ' . $this->db->sql_in_set('user2_id', $user1_ids));
                $this->db->sql_query('UPDATE `' . $this->db->dreamteams_table . '` SET `' . $col2 . '` = `' . $col2 . '` + 1 WHERE `user1_id` < `user2_id` AND ' . $this->db->sql_in_set('user1_id', $user2_ids) . ' AND ' . $this->db->sql_in_set('user2_id', $user2_ids));

                $user_ids = \array_merge($user1_ids, $user2_ids);

                if (!$repost) {
                    // note: in post_undo, there is no undo of the player_maps
                    // table so we dont do this on repost
                    $this->update_user_map_counter($map_id, $user_ids);
                }

                $this->bets->evaluate_bets($winner === 1 ? $team1_id : $team2_id, $winner === 1 ? $team2_id : $team1_id, $end_time);
            } else {
                foreach($this->get_teams_players($team1_id, $team2_id)->items() as $mp) {
                    $this->players->fix_streaks($mp->get_id(), $min_team_id); // note: this isn't needed for normal game posting, but for fixing matches
                }

                $winner_team_id = 0;
            }

            $update_sql = [
                'post_user_id' => $post_user_id,
                'post_time' => $end_time,
                'winner_team_id' => $winner_team_id,
            ];

            // we don't want to create duplicate topics for a match
            $forum_id = $this->config->get(config::match_forum_id);
            if ($forum_id) {
                $forum_topic_id = (int) $row['forum_topic_id'];

                $title = $this->create_match_topic_title($match_id, $team1_id, $team2_id, $winner);
                $message = $this->create_match_shortcode($match_id);
                $update_sql['forum_topic_id'] = $this->create_topic_and_post(
                    $title,
                    $message,
                    $forum_id,
                    $forum_topic_id
                );
            }

            $this->db->update($this->db->matches_table, $update_sql, [
                'match_id' => $match_id
            ]);
        };

        $this->locks->runExclusive(
            ['action' => 'post', 'match_id' => $match_id],
            $this->db->txn_fn($fn),
            120
        );
    }

    protected function update_user_map_counter(int $map_id, array $user_ids) {
        // set the just played map to 0
        $this->db->update(
            $this->db->player_map_table,
            ['counter' => 0],
            $this->db->sql_in_set('user_id', $user_ids) . ' AND `map_id` = ' . $map_id
        );

        $users_allowed_to_veto = $this->acl->get_users_permission($user_ids, acl::u_zone_veto_maps);

        // increase all other maps by their weight
        foreach($user_ids as $user_id) {
            $do_not_update_map_ids = [$map_id];

            if (\in_array($user_id, $users_allowed_to_veto)) {
                $veto_map_ids = $this->db->get_col([
                    'SELECT' => 't.map_id',
                    'FROM' => [$this->db->player_map_table => 't'],
                    'WHERE' => 't.veto = 1 AND t.user_id = ' . $user_id,
                ]);
                $do_not_update_map_ids = \array_merge($do_not_update_map_ids, $veto_map_ids);
            }

            $this->db->sql_query('
                UPDATE ' . $this->db->player_map_table . ' mp
                SET counter = counter + (SELECT weight FROM ' . $this->db->maps_table . ' m WHERE m.map_id = mp.map_id)
                WHERE ' . $this->db->sql_in_set('mp.map_id', $do_not_update_map_ids, true) . ' AND user_id = '. $user_id
            );
        }
    }

    /**
     * Create a topic and first post.
     *
     * Note: If topic already exists, this only updates title of
     * topic and first post.
     *
     * @param string $title
     * @param string $message
     * @param int $forum_id
     * @param int $forum_topic_id
     *
     * @return int
     */
    protected function create_topic_and_post(
        string $title,
        string $message,
        int $forum_id,
        int $forum_topic_id
    ): int {
        return $this->misc->create_post(
            $title,
            $message,
            $forum_id,
            $forum_topic_id
        );
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
        $match_points_team1 = $match_points[1];
        $match_points_team2 = $match_points[2];

        foreach ($team1_list->items() as $mp) {
            $this->players->match_changes_undo(
                $mp->get_id(),
                $team1_id,
                $match_points_team1,
                $team1_id === $winner_team_id
            );
        }
        foreach ($team2_list->items() as $mp) {
            $this->players->match_changes_undo(
                $mp->get_id(),
                $team2_id,
                $match_points_team2,
                $team2_id === $winner_team_id
            );
        }

        $user1_ids = $team1_list->get_ids();
        $col1 = $winner_team_id == $team1_id ? 'matches_won' : 'matches_loss';
        $this->db->sql_query('UPDATE `' . $this->db->dreamteams_table . '` SET `' . $col1 . '` = `' . $col1 . '` - 1 WHERE `user1_id` < `user2_id` AND ' . $this->db->sql_in_set('user1_id', $user1_ids) . ' AND ' . $this->db->sql_in_set('user2_id', $user1_ids));

        $user2_ids = $team2_list->get_ids();
        $col2 = $winner_team_id == $team2_id ? 'matches_won' : 'matches_loss';
        $this->db->sql_query('UPDATE `' . $this->db->dreamteams_table . '` SET `' . $col2 . '` = `' . $col2 . '` - 1 WHERE `user1_id` < `user2_id` AND ' . $this->db->sql_in_set('user1_id', $user2_ids) . ' AND ' . $this->db->sql_in_set('user2_id', $user2_ids));

        $this->bets->evaluate_bets_undo(
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
        return $this->db->get_int_col([
            'SELECT' => 't.team_id',
            'FROM' => [$this->db->match_teams_table => 't'],
            'WHERE' => 't.match_id = ' . $match_id,
            'ORDER_BY' => 't.match_team ASC',
        ]);
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
        $match_players = entity\match_player::array_by_rows($rows);
        return entity\match_players_list::from_match_players($match_players);
    }

    private function get_match_points_by_match_size(
        int $match_size,
        int $rating_diff,
        int $winner
    ): array {
        $free_points = (int)$this->config->get(config::free_points);
        $free_points_team_1 = $winner === 1 ? $free_points : 0;
        $free_points_team_2 = $winner === 2 ? $free_points : 0;

        $base_points = $this->config->base_points_by_match_size($match_size);
        if ($base_points < 0) {
            if ((bool)$this->config->get(config::free_points_unrated)) {
                return [
                    1 => max($free_points_team_1, 0),
                    2 => max($free_points_team_2, 0)
                ];
            } else {
                return [1 => 0, 2 => 0];
            }
        }

        $is_outsider_win = ($rating_diff > 0 xor $winner === 1) ? true : false;
        $factor = $is_outsider_win ? 1 : -1;
        $extra_points = (int)floor(abs($rating_diff) / (float)$this->config->get(config::extra_points));

        $regular_match_points = max(0, $base_points + $factor * $extra_points);

        if ($regular_match_points === 0 and (bool)$this->config->get(config::free_points_difference)) {
            return [
                1 => max($free_points_team_1, 0),
                2 => max($free_points_team_2, 0)
            ];
        }
        if ($regular_match_points === 0) {
            return [
                1 => 0,
                2 => 0
            ];
        }

        if ((bool)$this->config->get(config::free_points_regular)) {
            return [
                1 => max($regular_match_points + $free_points_team_1, 0),
                2 => max($regular_match_points + $free_points_team_2, 0)
            ];
        } else {
            return [
                1 => $regular_match_points,
                2 => $regular_match_points
            ];
        }
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
    m.description as map_description,
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

    public function get_match(int $match_id, bool $with_bets = True, bool $with_description = True): entity\match
    {
        $rows = $this->load_match_rows(["t.match_id = {$match_id}"]);
        if (empty($rows)) {
            return null;
        }
        return entity\match::create_by_row_finished($rows[0], $with_bets, $with_description);
    }

    public function get_all_rmatches($with_bets = False, bool $with_description = True): array
    {
        $rows = $this->load_match_rows(['t.post_time = 0'], ['t.draw_time DESC']);
        return entity\match::array_by_rows_unfinished($rows, $with_bets, $with_description);
    }

    public function get_pmatches(int $page = 0, bool $with_bets = True, bool $with_description = True): array
    {
        $limit = $this->get_pmatches_page_size();
        $offset = $page * $limit;
        $rows = $this->load_match_rows(['t.post_time > 0'], ['t.post_time DESC'], $offset, $limit);
        return entity\match::array_by_rows_finished($rows, $with_bets, $with_description);
    }

    public function get_pmatches_total_pages(): int
    {
        $num_matches = $this->get_pmatches_count();
        $page_size = $this->get_pmatches_page_size();
        return ceil($num_matches / $page_size);
    }

    private function get_pmatches_page_size(): int
    {
        return (int) $this->config->get(config::pmatches_page_size);
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

    public function get_banned_civs(int $match_id, int $map_id): array
    {
        $free_pick_civ_id = (int) $this->config->get(config::free_pick_civ_id);
        return $this->match_has_civ_id($match_id, $free_pick_civ_id)
            ? $this->maps->get_banned_civs($map_id)
            : [];
    }

    private function match_has_civ_id(int $match_id, int $civ_id): bool
    {
        //test if teams have civ_id
        $team_ids = $this->get_match_team_ids($match_id);
        if (\in_array($civ_id, $this->db->get_int_col([
            'SELECT' => 'civ_id',
            'FROM' => [$this->db->match_team_civs_table => 't'],
            'WHERE' => ['team_id' => ['$in' => $team_ids]],
        ]), true)) {
            return true;
        }

        //test if some player has civ_id
        if (\in_array($civ_id, $this->db->get_int_col([
            'SELECT' => 'civ_id',
            'FROM' => [$this->db->match_player_civs_table => 't'],
            'WHERE' => ['match_id' => $match_id],
        ]), true)) {
            return true;
        }

        //test if some match has civ_id
        if (\in_array($civ_id, $this->get_civ_ids_by_match($match_id), true)) {
            return true;
        }

        return false;
    }

    /**
     * @param int $match_id
     *
     * @return int[]
     */
    private function get_civ_ids_by_match(int $match_id): array
    {
        return $this->db->get_int_col([
            'SELECT' => 'civ_id',
            'FROM' => [$this->db->match_civs_table => 't'],
            'WHERE' => ['match_id' => $match_id],
        ]);
    }

    public function is_over(int $match_id): bool
    {
        return $this->get_pmatches_count(['match_id' => $match_id]) > 0;
    }

    private function get_pmatches_count(array $where = []): int
    {
        return $this->db->get_int_var([
            'SELECT' => 'COUNT(*)',
            'FROM' => [$this->db->matches_table => 't'],
            'WHERE' => \array_merge(
                $where,
                ['post_time' => ['$gt' => 0]]
            ),
        ]);
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
        return $this->db->get_int_var($sql);
    }

    public function is_any_draw_process_active(): bool
    {
        return $this->get_draw_id_by_user_id() > 0;
    }

    public function get_draw_id_by_user_id(int $user_id = 0): int
    {
        $row = $this->db->get_row([
            'SELECT' => 'draw_id, time',
            'FROM' => [$this->db->draw_process_table => 't'],
            'WHERE' => $user_id ? ['user_id' => $user_id] : [],
        ]);
        if (!$row) {
            return 0;
        }

        $draw_process_time = (int)$row['time'];
        if ($draw_process_time && ((time() - $draw_process_time) > $this->config->get(config::draw_time))) {
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
        $match_players = entity\match_player::array_by_rows($rows);
        return entity\match_players_list::from_match_players($match_players);
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

            $players = $this->players->get_logged_in();
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
        $this->db->truncate($this->db->draw_process_table);
        $this->db->truncate($this->db->draw_players_table);
    }

    /**
     * @param int $user_id
     * @return int[] Match ids created by draw process
     * @throws \Throwable
     */
    public function draw(int $user_id): array
    {
        $fn = function () use ($user_id) {
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
            $factor = $this->get_draw_factor();
            $switch_player_numbers = $this->get_draw_switch_player_numbers();
            $draw_matches = $this->draw_teams->make_matches(
                $players_list,
                $factor,
                $switch_player_numbers[0],
                $switch_player_numbers[1]
            );
            foreach ($draw_matches as $draw_match) {
                $setting = $this->draw_settings->draw_settings($user_id, $draw_match);
                $match_ids[] = $this->create_match($setting);

                foreach ($draw_match->get_all_player_ids() as $player_id) {
                    $user_ids[] = $player_id;
                }
            }
            $this->players->logout_players(...$user_ids);
            return $match_ids;
        };
        return $this->locks->runExclusive(
            ['action' => 'draw'],
            $this->db->txn_fn($fn),
            120
        );
    }

    public function get_draw_user_id(int $match_id): int
    {
         return $this->db->get_int_var([
             'SELECT' => 'draw_user_id',
             'FROM' => [$this->db->matches_table => 't'],
             'WHERE' => ['match_id' => $match_id],
         ]);
    }

    /**
     * @param int $match_id
     * @param int $team1_id
     * @param int $team2_id
     * @param int $winner
     *
     * @return string
     */
    protected function create_match_topic_title(
        int $match_id,
        int $team1_id,
        int $team2_id,
        int $winner
    ): string {
        $team_names = $this->players->get_team_usernames($team1_id, $team2_id);
        if ($winner === 1) {
            $t1_str = ' (W)';
            $t2_str = ' (L)';
        } elseif ($winner === 2) {
            $t1_str = ' (L)';
            $t2_str = ' (W)';
        } else {
            $t1_str = '';
            $t2_str = '';
        }

        $t1_names = \implode(', ', $team_names[$team1_id]);
        $t2_names = \implode(', ', $team_names[$team2_id]);

        return "[#{$match_id}] {$t1_names}{$t1_str} vs. {$t2_names}{$t2_str}";
}

    private function create_match_shortcode(int $match_id): string
    {
        return "[match]{$match_id}[/match]";
    }
}
