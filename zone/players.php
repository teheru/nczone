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

class players
{
    /** @var driver_interface */
    private $db;
    /** @var \phpbb\user */
    private $user;
    /** @var string */
    private $players_table;
    /** @var string */
    private $users_table;
    /** @var string */
    private $match_players_table;
    /** @var string */
    private $match_player_civs_table;
    /** @var string */
    private $maps_table;
    /** @var string */
    private $civs_table;
    /** @var string */
    private $map_civs_table;
    /** @var string */
    private $player_map_table;
    /** @var string */
    private $player_civ_table;
    /** @var string */
    private $bets_table;

    /**
     * nC Zone players management class.
     * @param driver_interface $db
     * @param \phpbb\user $user
     * @param string $players_table
     * @param string $users_table
     * @param string $match_players_table
     * @param string $match_player_civs_table
     * @param string $maps_table
     * @param string $civs_table
     * @param string $map_civs_table
     * @param string $player_map_table
     * @param string $player_civ_table
     * @param string $bets_table
     */
    public function __construct(driver_interface $db, \phpbb\user $user, string $players_table,
                                string $users_table, string $match_players_table, string $match_player_civs_table,
                                string $maps_table, string $civs_table, string $map_civs_table, string $player_map_table,
                                string $player_civ_table, string $bets_table)
    {
        $this->db = $db;
        $this->user = $user;
        $this->players_table = $players_table;
        $this->users_table = $users_table;
        $this->match_players_table = $match_players_table;
        $this->match_player_civs_table = $match_player_civs_table;
        $this->maps_table = $maps_table;
        $this->civs_table = $civs_table;
        $this->map_civs_table = $map_civs_table;
        $this->player_map_table = $player_map_table;
        $this->player_civ_table = $player_civ_table;
        $this->bets_table = $bets_table;
    }

    public static function sort_by_ratings(array $players): array
    {
        usort($players, function ($p1, $p2) {
            return number_util::cmp($p1['rating'], $p2['rating']);
        });
        return $players;
    }

    public static function get_rating_sum(array $players): int
    {
        return array_reduce($players, function ($p1, $p2) {
            return $p1 + $p2['rating'];
        });
    }

    public static function get_max_rating(...$players_lists): int
    {
        $max = PHP_INT_MIN;
        foreach ($players_lists as $player_list) {
            foreach ($player_list as $player) {
                if ($player['rating'] > $max) {
                    $max = $player['rating'];
                }
            }
        }
        return $max === PHP_INT_MIN ? 0 : $max;
    }

    public static function get_min_rating(...$players_lists): int
    {
        $min = PHP_INT_MAX;
        foreach ($players_lists as $player_list) {
            foreach ($player_list as $player) {
                if ($player['rating'] < $min) {
                    $min = $player['rating'];
                }
            }
        }
        return $min === PHP_INT_MAX ? 0 : $min;
    }

    /**
     * Returns the zone information of a user/player
     *
     * @param int    $user_id    The id of the user
     *
     * @return array
     */
    public function get_player(int $user_id): array
    {
        $playerRow = db_util::get_row($this->db, [
            'SELECT' => 'p.logged_in AS logged_in, p.rating AS rating, p.matches_won AS matches_won, p.matches_loss AS matches_loss, p.bets_won AS bets_won, p.bets_loss AS bets_loss',
            'FROM' => [$this->players_table => 'p'],
            'WHERE' => 'p.user_id = ' . $user_id
        ]) ?: [];

        $userRow = db_util::get_row($this->db, [
            'SELECT' => 'u.username AS username',
            'FROM' => [$this->users_table => 'u'],
            'WHERE' => 'u.user_id = ' . $user_id,
        ]) ?: [];
        return array_merge($playerRow, $userRow);
    }

    /**
     * Creates an entry for an user for the zone
     *
     * @param int    $user_id    Id of the user
     * @param int    $rating     Start rating for the user
     *
     * @return bool
     */
    public function activate_player(int $user_id, int $rating): bool
    {
        $count = (int)db_util::get_var($this->db, [
            'SELECT' => 'COUNT(*) AS n',
            'FROM' => [$this->players_table => 'u'],
            'WHERE' => 'u.user_id = ' . $user_id,
        ]);
        if ($count > 0) {
            return false;
        }

        db_util::insert($this->db, $this->players_table, [
            'user_id' => $user_id,
            'rating' => $rating,
            'logged_in' => 0,
            'matches_won' => 0,
            'matches_loss' => 0,
            'bets_won' => 0,
            'bets_loss' => 0
        ]);

        $this->db->sql_query('INSERT INTO `'. $this->player_map_table .'` (`user_id`, `map_id`, `time`) SELECT "'. $user_id .'", map_id, "'. time() .'" FROM `'. $this->maps_table .'`');
        $this->db->sql_query('INSERT INTO `'. $this->player_civ_table .'` (`user_id`, `civ_id`, `time`) SELECT "'. $user_id .'", civ_id, "'. time() .'" FROM `'. $this->civs_table .'`');

        return true;
    }

    /**
     * Edits the information of a player
     *
     * @param int      $user_id        Id of the user
     * @param array    $player_info    Information array for the user
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

        db_util::update($this->db, $this->players_table, $sql_array, ['user_id' => $user_id]);
    }

    /**
     * Logins a player
     *
     * @param int    $user_id    Id of the user
     *
     * @return void
     */
    public function login_player(int $user_id): void
    {
        $this->edit_player($user_id, ['logged_in' => time()]);
    }

    /**
     * Logouts a player
     *
     * @param int    $user_id    Id of the user
     *
     * @return void
     */
    public function logout_player(int $user_id): void
    {
        $this->edit_player($user_id, ['logged_in' => 0]);
    }

    public function logout_players(array $user_ids): void
    {
        db_util::update($this->db, $this->players_table, ['logged_in' => 0], $this->db->sql_in_set('user_id', $user_ids));
    }

    public function place_bet(int $user_id, int $match_id, int $team_id): void
    {
        [$team1_id, $team2_id] = zone_util::matches()->get_match_team_ids($match_id);
        if($team_id === $team1_id || $team_id === $team2_id)
        {
            $num_bets = (int)db_util::get_var($this->db, [
                'SELECT' => 'COUNT(*)',
                'FROM' => [$this->bets_table => 't'],
                'WHERE' => 'user_id = ' . $user_id . ' AND (team_id = ' . $team1_id . ' OR team_id = ' . $team2_id . ')'
            ]);

            if($num_bets === 0)
            {
                db_util::insert($this->db, $this->bets_table, [
                    'user_id' => $user_id,
                    'team_id' => $team_id,
                    'time' => time(),
                ]);
            }
        }
    }

    public function match_changes(int $user_id, int $rating_change, bool $winner): void
    {
        $col = $winner ? '`matches_won`' : '`matches_loss`';

        $this->db->sql_query('UPDATE `' . $this->players_table . '` SET `rating` = `rating` + ' . (($winner ? 1 : -1) * $rating_change) . ', ' . $col . ' = ' . $col . ' + 1 WHERE `user_id` = ' . $user_id);
    }

    public function match_changes_undo(int $user_id, int $rating_change, bool $former_winner): void
    {
        $col = $former_winner ? '`matches_won`' : '`matches_loss`';

        $this->db->sql_query('UPDATE `' . $this->players_table . '` SET `rating` = `rating` + ' . (($former_winner ? -1 : 1) * $rating_change) . ', ' . $col . ' = ' . $col . ' - 1 WHERE `user_id` = ' . $user_id);
    }

    /**
     * Gets the user id, name and rating of all logged in players
     *
     * @return array
     */
    public function get_logged_in(): array
    {
        $rows = db_util::get_rows($this->db, [
            'SELECT' => 'p.user_id AS id, u.username, p.rating, p.logged_in',
            'FROM' => [$this->players_table => 'p', $this->users_table => 'u'],
            'WHERE' => 'logged_in > 0 AND p.user_id = u.user_id',
            'ORDER_BY' => 'logged_in DESC'
        ]);
        return array_map(function($row) {
            return [
                'id' => (int)$row['id'],
                'username' => $row['username'],
                'rating' => (int)$row['rating'],
                'logged_in' => (int)$row['logged_in'],
            ];
        }, $rows);
    }

    /**
     * Gets the user id, name and rating of all players
     *
     * @return array
     */
    public function get_all(): array
    {
        $rows = db_util::get_rows($this->db, [
            'SELECT' => 'p.user_id AS id, u.username, p.rating, p.logged_in',
            'FROM' => [$this->players_table => 'p', $this->users_table => 'u'],
            'WHERE' => 'p.user_id = u.user_id',
            'ORDER_BY' => 'username ASC'
        ]);
        return array_map(function($row) {
            return [
                'id' => (int)$row['id'],
                'username' => $row['username'],
                'rating' => (int)$row['rating'],
                'logged_in' => (int)$row['logged_in'],
            ];
        }, $rows);
    }

    public function get_match_players(int $match_id, int $team1_id, int $team2_id, int $map_id): array
    {
        $result = $this->db->sql_query('
            SELECT 
                mp.user_id as id, 
                mp.team_id,
                u.username AS name, 
                mp.draw_rating AS rating, 
                mp.rating_change, 
                pc.civ_id, 
                c.civ_name,
                mc.multiplier
            FROM 
                ' . $this->match_players_table . ' mp
                INNER JOIN ' . $this->users_table . ' u ON u.user_id = mp.user_id
                LEFT JOIN ' . $this->match_player_civs_table . ' pc ON pc.user_id = mp.user_id AND pc.match_id = ' . $match_id . '
                LEFT JOIN ' . $this->civs_table . ' c ON c.civ_id = pc.civ_id
                LEFT JOIN ' . $this->map_civs_table . ' mc ON mc.map_id = ' . $map_id . ' AND mc.civ_id = c.civ_id
            WHERE 
                mp.team_id IN (' . $team1_id . ', ' . $team2_id . ')
            ORDER BY
                rating DESC
        ');
        $player_rows = $this->db->sql_fetchrowset($result);
        $this->db->sql_freeresult($result);

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
        return array_key_exists('rating', $this->get_player($user_id));
    }
}
