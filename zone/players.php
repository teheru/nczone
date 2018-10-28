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

class players
{
    /** @var \phpbb\user */
    private $user;
    /** @var db */
    private $db;

    /**
     * nC Zone players management class.
     *
     * @param \phpbb\user $user
     * @param db $db
     */
    public function __construct(\phpbb\user $user, db $db)
    {
        $this->user = $user;
        $this->db = $db;
    }

    /**
     * Returns the zone information of a user/player
     *
     * @param int $user_id The id of the user
     *
     * @return player
     */
    public function get_player(int $user_id): player
    {
        $row = $this->db->get_row('
            SELECT
                u.username AS username,
                p.user_id AS id,
                p.rating AS rating,
                p.logged_in AS logged_in,
                MAX(s.session_time) AS last_activity,
                p.matches_won AS matches_won,
                p.matches_loss AS matches_loss,
                COALESCE(t.rating_change, 0) AS rating_change,
                COALESCE(t.streak, 0) AS streak,
                p.bets_won AS bets_won,
                p.bets_loss AS bets_loss,
                p.activity
            FROM
                ' . $this->db->players_table . ' p
                INNER JOIN ' . $this->db->users_table . ' u 
                ON u.user_id = p.user_id
                
                LEFT JOIN ' . $this->db->session_table . ' s 
                ON s.session_user_id = p.user_id
                
                LEFT JOIN ' . $this->db->match_players_table . ' t 
                ON t.user_id = p.user_id 
                AND t.team_id = (SELECT MAX(team_id) FROM ' . $this->db->match_players_table . ' WHERE user_id = p.user_id AND rating_change != 0)
            WHERE
                u.user_id = ' . $user_id.'
            ;
        ');
        return player::create_by_row($row);
    }

    /**
     * Creates an entry for an user for the zone
     *
     * @param int $user_id Id of the user
     * @param int $rating Start rating for the user
     *
     * @return bool
     * @throws \Throwable
     */
    public function activate_player(int $user_id, int $rating): bool
    {
        return $this->db->run_txn(function () use ($user_id, $rating) {
            $count = (int)$this->db->get_var([
                'SELECT' => 'COUNT(*) AS n',
                'FROM' => [$this->db->players_table => 'u'],
                'WHERE' => 'u.user_id = ' . $user_id,
            ]);
            if ($count > 0) {
                return false;
            }

            $this->db->insert($this->db->players_table, [
                'user_id' => $user_id,
                'rating' => $rating,
                'logged_in' => 0,
                'matches_won' => 0,
                'matches_loss' => 0,
                'bets_won' => 0,
                'bets_loss' => 0,
                'activity' => 0,
            ]);

            $this->db->sql_query(
                'INSERT INTO `' . $this->db->dreamteams_table . '` (`user1_id`, `user2_id`, `matches_won`, `matches_loss`) ' .
                'SELECT `user_id`, ' . $user_id . ', 0, 0 FROM `' . $this->db->players_table . '` WHERE `user_id` < ' . $user_id
            );

            $this->db->sql_query(
                'INSERT INTO `' . $this->db->dreamteams_table . '` (`user1_id`, `user2_id`, `matches_won`, `matches_loss`) ' .
                'SELECT ' . $user_id . ', `user_id`, 0, 0 FROM `' . $this->db->players_table . '` WHERE `user_id` > ' . $user_id
            );

            $this->db->sql_query(
                'INSERT INTO `' . $this->db->player_map_table . '` (`user_id`, `map_id`, `time`) ' .
                'SELECT "' . $user_id . '", map_id, "' . time() . '" FROM `' . $this->db->maps_table . '`'
            );

            $this->db->sql_query(
                'INSERT INTO `' . $this->db->player_civ_table . '` (`user_id`, `civ_id`, `time`) ' .
                'SELECT "' . $user_id . '", civ_id, "' . time() . '" FROM `' . $this->db->civs_table . '`'
            );

            return true;
        });
    }

    /**
     * Edits the information of a player
     *
     * @param int $user_id Id of the user
     * @param array $player_info Information array for the user
     *
     * @return void
     */
    public function edit_player(int $user_id, array $player_info): void
    {
        $sql_array = [];
        if (array_key_exists('rating', $player_info)) {
            $sql_array['rating'] = (int)$player_info['rating'];
        }
        if (array_key_exists('logged_in', $player_info)) {
            $sql_array['logged_in'] = (int)$player_info['logged_in'];
        }
        if (array_key_exists('matches_won', $player_info)) {
            $sql_array['matches_won'] = (int)$player_info['matches_won'];
        }
        if (array_key_exists('matches_loss', $player_info)) {
            $sql_array['matches_loss'] = (int)$player_info['matches_loss'];
        }
        if (array_key_exists('bets_won', $player_info)) {
            $sql_array['bets_won'] = (int)$player_info['bets_won'];
        }
        if (array_key_exists('bets_loss', $player_info)) {
            $sql_array['bets_loss'] = (int)$player_info['bets_loss'];
        }
        if (array_key_exists('activity', $player_info)) {
            $sql_array['activity'] = (int)$player_info['activity'];
        }

        $this->db->update($this->db->players_table, $sql_array, ['user_id' => $user_id]);
    }

    /**
     * Logins a player
     *
     * @param int $user_id Id of the user
     *
     * @return void
     */
    public function login_player(int $user_id): void
    {
        $this->edit_player($user_id, ['logged_in' => time()]);
    }

    public function in_match(int $user_id): bool
    {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                ' . $this->db->match_players_table . ' p
                INNER JOIN ' . $this->db->match_teams_table . ' t
                ON t.team_id = p.team_id
                INNER JOIN '.$this->db->matches_table.' m
                ON m.match_id = t.match_id
                AND m.post_time = 0
            WHERE
                user_id = ' . $user_id . '
            ;
        ';
        return (bool)$this->db->get_var($sql);
    }

    /**
     * Logouts a player
     *
     * @param int $user_id Id of the user
     *
     * @return void
     */
    public function logout_player(int $user_id): void
    {
        $this->edit_player($user_id, ['logged_in' => 0]);
    }

    public function logout_players(int ...$user_ids): void
    {
        $this->db->update($this->db->players_table, ['logged_in' => 0], $this->db->sql_in_set('user_id', $user_ids));
    }

    public function place_bet(int $user_id, int $match_id, int $team): void
    {
        $team_ids = zone_util::matches()->get_match_team_ids($match_id);
        $this->db->insert($this->db->bets_table, [
            'user_id' => $user_id,
            'time' => time(),
            'team_id' => $team_ids[$team - 1],
            'counted' => 0,
        ]);
    }

    public function has_bet(int $user_id, int $match_id): bool
    {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                ' . $this->db->bets_table . ' b
                INNER JOIN ' . $this->db->match_teams_table . ' t 
                ON b.team_id = t.team_id
                INNER JOIN ' . $this->db->matches_table . ' m 
                ON m.match_id = t.match_id AND m.match_id = ' . $match_id . ' 
            WHERE
                b.user_id = ' . $user_id . '
            ;
        ';
        return (int)$this->db->get_var($sql);
    }

    public function match_changes(int $user_id, int $team_id, int $match_points, bool $winner): void
    {
        $col = $winner ? '`matches_won`' : '`matches_loss`';

        $this->db->sql_query('UPDATE `' . $this->db->players_table . '` SET `rating` = `rating` + ' . (($winner ? 1 : -1) * $match_points) . ', ' . $col . ' = ' . $col . ' + 1 WHERE `user_id` = ' . $user_id);
        $this->db->update($this->db->match_players_table, [
            'rating_change' => ($winner ? 1 : -1) * $match_points,
            'streak' => self::calculate_new_streak($winner, $this->last_streak($user_id)),
        ], [
            'user_id' => $user_id,
            'team_id' => $team_id,
        ]);
    }

    public function match_changes_undo(int $user_id, int $team_id, int $match_points, bool $winner): void
    {
        $col = $winner ? '`matches_won`' : '`matches_loss`';

        $this->db->sql_query('UPDATE `' . $this->db->players_table . '` SET `rating` = `rating` + ' . (($winner ? -1 : 1) * $match_points) . ', ' . $col . ' = ' . $col . ' - 1 WHERE `user_id` = ' . $user_id);
        $this->db->update($this->db->match_players_table, [
            'rating_change' => 0,
            'streak' => 0,
        ], [
            'user_id' => $user_id,
            'team_id' => $team_id,
        ]);
    }

    public function fix_streaks(int $user_id, int $match_id): void
    {
        $team_id = min(zone_util::matches()->get_match_team_ids($match_id));

        $last_streak = (int)$this->db->get_var([
            'SELECT' => 't.streak',
            'FROM' => [$this->db->match_players_table => 't'],
            'WHERE' => 't.rating_change != 0 AND t.user_id = ' . $user_id . ' AND t.team_id < ' . $team_id,
            'ORDER_BY' => 't.team_id DESC'
        ]);

        $rows = $this->db->get_rows([
            'SELECT' => 't.team_id, t.rating_change',
            'FROM' => [$this->db->match_players_table => 't'],
            'WHERE' => 't.rating_change != 0 AND t.user_id = ' . $user_id . ' AND t.team_id >= ' . $team_id,
            'ORDER_BY' => 't.team_id ASC'
        ]);

        foreach ($rows as $r) {
            $won = (int)$r['rating_change'] > 0;

            $new_streak = self::calculate_new_streak($won, $last_streak);
            $this->db->update($this->db->match_players_table, [
                'streak' => $new_streak,
            ], [
                'user_id' => $user_id,
                'team_id' => $r['team_id'],
            ]);
            $last_streak = $new_streak;
        }
    }

    public function last_streak(int $user_id): int
    {
        return (int)$this->db->get_var([
            'SELECT' => 't.streak',
            'FROM' => [$this->db->match_players_table => 't'],
            'WHERE' => 't.rating_change != 0 AND t.user_id = ' . $user_id,
            'ORDER_BY' => 't.team_id DESC',
        ]);
    }

    /**
     * Gets the user id, name and rating of all logged in players
     *
     * @return player[]
     */
    public function get_logged_in(): array
    {
        return $this->get_players(['logged_in' => true]);
    }

    /**
     * Gets the user id, name and rating of all players
     *
     * @param int $min_matches Minimum count of matches that the returned players must have.
     * @return player[]
     */
    public function get_all(int $min_matches = 0): array
    {
        return $this->get_players(['min_matches' => $min_matches]);
    }

    private function get_players(array $filter): array
    {
        $where = [];
        if (isset($filter['logged_in']) && $filter['logged_in']) {
            $where[] = 'p.logged_in > 0';
        }

        if (isset($filter['min_matches'])) {
            $where[] = '(p.matches_won + p.matches_loss) >= ' . (int)$filter['min_matches'];
        }

        $rows = $this->db->get_rows('
            SELECT
                p.user_id AS id, 
                u.username, 
                p.rating, 
                p.logged_in, 
                p.matches_won, 
                p.matches_loss, 
                p.activity, 
                MAX(s.session_time) AS last_activity,
                COALESCE(t.rating_change, 0) AS rating_change,
                COALESCE(t.streak, 0) AS streak,
                p.bets_won AS bets_won,
                p.bets_loss AS bets_loss,
                p.activity
            FROM
                ' . $this->db->players_table . ' p
                INNER JOIN ' . $this->db->users_table . ' u 
                ON u.user_id = p.user_id
                
                LEFT JOIN ' . $this->db->session_table . ' s 
                ON s.session_user_id = p.user_id
                
                LEFT JOIN ' . $this->db->match_players_table . ' t 
                ON t.user_id = p.user_id 
                AND t.team_id = (SELECT MAX(team_id) FROM ' . $this->db->match_players_table . ' WHERE user_id = p.user_id AND rating_change != 0)
            WHERE
                '.(empty($where) ? '1=1' : implode(' AND ', $where)).'
            GROUP BY
                p.user_id
            ;
        ');
        return array_map([player::class, 'create_by_row'], $rows);
    }

    public function get_match_players(int $match_id, int $team1_id, int $team2_id, int $map_id): array
    {
        $player_rows = $this->db->get_rows('
            SELECT 
                mp.user_id as id, 
                mp.team_id,
                u.username AS username, 
                mp.draw_rating AS rating, 
                mp.rating_change, 
                pc.civ_id, 
                c.civ_name,
                mc.multiplier
            FROM 
                ' . $this->db->match_players_table . ' mp
                INNER JOIN ' . $this->db->users_table . ' u ON u.user_id = mp.user_id
                LEFT JOIN ' . $this->db->match_player_civs_table . ' pc ON pc.user_id = mp.user_id AND pc.match_id = ' . $match_id . '
                LEFT JOIN ' . $this->db->civs_table . ' c ON c.civ_id = pc.civ_id
                LEFT JOIN ' . $this->db->map_civs_table . ' mc ON mc.map_id = ' . $map_id . ' AND mc.civ_id = c.civ_id
            WHERE 
                mp.team_id IN (' . $team1_id . ', ' . $team2_id . ')
            ORDER BY
                rating DESC
        ');

        $teams = [];
        foreach($player_rows as $r)
        {
            $r['id'] = (int)$r['id'];

            $civ_id = (int)$r['civ_id'];
            $civ_name = $r['civ_name'];
            unset($r['civ_id'], $r['civ_name']);
            if($civ_id)
            {
                $r['civ'] = ['id' => $civ_id, 'title' => $civ_name];
            }

            $team_id = (int)$r['team_id'];
            unset($r['team_id']);

            $r['rating'] = (int)$r['rating'];
            $r['rating_change'] = (int)$r['rating_change'];
            $r['multiplier'] = (float)$r['multiplier'];

            $teams[$team_id][] = $r;
        }

        [$team1_key, $team2_key] = array_keys($teams);

        return [
            'team1' => $teams[$team1_key] ?? [],
            'team2' => $teams[$team2_key] ?? [],
        ];
    }

    public function is_activated(int $user_id): bool
    {
        return $this->get_player($user_id)->is_activated();
    }

    public function get_team_usernames(int ...$team_ids): array
    {
        if(!$team_ids)
        {
            return [];
        }

        $team_usernames = [];
        foreach($team_ids as $team_id)
        {
            $team_usernames[$team_id] = [];
        }

        $rows = $this->db->get_rows([
            'SELECT' => 'mp.team_id, u.username',
            'FROM' => [$this->db->match_players_table => 'mp', $this->db->users_table => 'u'],
            'WHERE' => 'mp.user_id = u.user_id AND ' . $this->db->sql_in_set('team_id', $team_ids),
            'ORDER_BY' => 'mp.draw_rating DESC',
        ]);

        foreach($rows as $r)
        {
            $team_username[(int)$r['team_id']][] = $r['username'];
        }
        return $team_username;
    }

    public function set_player_language(int $user_id, string $lang): void
    {
        $this->db->update($this->db->users_table, ['user_lang' => $lang], ['user_id' => $user_id]);
    }

    public static function calculate_new_streak(bool $winner, int $last_streak)
    {
        /** @noinspection NestedTernaryOperatorInspection */
        return $winner
            ? ($last_streak > 0 ? $last_streak + 1 : 1)
            : ($last_streak < 0 ? $last_streak - 1 : -1);
    }

    public function calculate_all_activities(): void
    {
        $min_time = time() - (int)config::get('nczone_activity_time') * 60 * 60 * 24;
        $sql = '
            SELECT
                user_id,
                COUNT(*) AS activity_matches
            FROM
                ' . $this->db->match_players_table . ' p
                INNER JOIN ' . $this->db->match_teams_table . ' t ON p.team_id = t.team_id
                INNER JOIN ' . $this->db->matches_table . ' m ON m.match_id = t.match_id
                AND m.post_time > '.$min_time.' AND m.winner_team_id != 0
            GROUP BY 
                p.user_id
            ;
        ';
        $rows = $this->db->get_rows($sql);

        foreach($rows as $row) {
            $user_id = (int)$row['user_id'];
            $activity_matches = (int)$row['activity_matches'];
            if($activity_matches >= (int)config::get('nczone_activity_5')) {
                $activity = 5;
            } elseif($activity_matches >= (int)config::get('nczone_activity_4')) {
                $activity = 4;
            } elseif($activity_matches >= (int)config::get('nczone_activity_3')) {
                $activity = 3;
            } elseif($activity_matches >= (int)config::get('nczone_activity_2')) {
                $activity = 2;
            } elseif($activity_matches >= (int)config::get('nczone_activity_1')) {
                $activity = 1;
            } else {
                $activity = 0;
            }

            $this->edit_player($user_id, ['activity' => $activity]);
        }
    }

    public function get_running_match_id(int $user_id): int
    {
        $sql = '
            SELECT
                m.match_id
            FROM
                ' . $this->db->match_players_table . ' p
                INNER JOIN ' . $this->db->match_teams_table . ' t
                ON t.team_id = p.team_id
                INNER JOIN '.$this->db->matches_table.' m
                ON m.match_id = t.match_id
                AND m.post_time = 0
            WHERE
                user_id = ' . $user_id . '
            ;
        ';
        return $this->db->get_var($sql);
    }

    public function get_player_rating_data(int $user_id): array
    {
        $sql = 'select
                  m.draw_time as time, draw_rating as rating
                from phpbb_zone_matches m
                  join phpbb_zone_match_teams t on m.match_id = t.match_id
                  join phpbb_zone_match_players p on t.team_id = p.team_id
                where user_id = '.$user_id.' and
                      winner_team_id != 0 and
                      post_time > 0';

        $match_numbers = [];
        $match_ratings = [];
        $match_times = [];

        $i = 0;
        foreach($this->db->get_rows($sql) as $row) {
            $match_numbers[] = ++$i;
            $match_ratings[] = (int)$row['rating'];
            $match_times[] = (int)$row['time'];
        }

        return [
            'numbers' => $match_numbers,
            'ratings' => $match_ratings,
            'times' => $match_times
        ];
    }
}
