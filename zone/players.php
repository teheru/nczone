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

    public function __construct(driver_interface $db, \phpbb\user $user, string $players_table, string $users_table)
    {
        $this->db = $db;
        $this->user = $user;
        $this->players_table = $players_table;
        $this->users_table = $users_table;
    }

    public function get_player(int $user_id): array
    {
        $playerRow = db_util::get_row($this->db, [
            'SELECT' => 'p.logged_in AS logged_in, p.rating AS rating, p.matches_won AS matches_won, p.matches_loss AS matches_loss',
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

    public function activate_player(int $user_id, int $rating)
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
            'matches_loss' => 0
        ]);
        return true;
    }

    public function edit_player(int $user_id, array $player_info)
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

        db_util::update($this->db, $this->players_table, $sql_array, ['user_id' => $user_id]);
    }

    public function login_player(int $user_id)
    {
        $this->edit_player($user_id, ['logged_in' => time()]);
    }

    public function logout_player(int $user_id)
    {
        $this->edit_player($user_id, ['logged_in' => 0]);
    }

    public function get_logged_in()
    {
        return db_util::get_rows($this->db, [
            'SELECT' => 'p.user_id AS user_id, u.username AS username, p.rating AS rating, p.logged_in AS logged_in',
            'FROM' => [$this->players_table => 'p', $this->users_table => 'u'],
            'WHERE' => 'logged_in > 0 AND p.user_id = u.user_id',
            'ORDER_BY' => 'logged_in DESC'
        ]);
    }

    public function get_players_map_id($user_ids)
    {
        return db_util::get_var($this->db, [
            'SELECT' => 't.map_id',
            'FROM' => ['zone_player_map_time' => 't', 'zone_maps' => 'm'],
            'WHERE' => 't.map_id = m.map_id AND ' . $this->db->sql_in_set('user_id', $user_ids),
            'GROUP_BY' => 't.map_id',
            'ORDER_BY' => 'SUM(UNIX_TIMESTAMP() - t.time) / m.weight DESC'
        ]);
    }
}
