<?php

namespace eru\nczone\utility;

use phpbb\db\driver\driver_interface;

class db
{
    /** @var driver_interface */
    private $db;

    /** @var string */
    public $users_table;
    /** @var string */
    public $session_table;
    /** @var string */
    public $posts_table;
    /** @var string */
    public $bets_table;
    /** @var string */
    public $players_table;
    /** @var string */
    public $dreamteams_table;
    /** @var string */
    public $match_players_table;
    /** @var string */
    public $match_player_civs_table;
    /** @var string */
    public $maps_table;
    /** @var string */
    public $civs_table;
    /** @var string */
    public $map_civs_table;
    /** @var string */
    public $player_map_table;
    /** @var string */
    public $player_civ_table;
    /** @var string */
    public $matches_table;
    /** @var string */
    public $match_teams_table;
    /** @var string */
    public $match_civs_table;
    /** @var string */
    public $match_team_civs_table;
    /** @var string */
    public $draw_process_table;
    /** @var string */
    public $draw_players_table;
    /** @var string */
    public $user_settings_table;

    public function __construct(driver_interface $db, string $table_prefix)
    {
        $this->db = $db;

        $this->users_table = $table_prefix . 'users';
        $this->session_table = $table_prefix . 'sessions';
        $this->posts_table = $table_prefix . 'posts';

        $this->bets_table = $table_prefix . 'zone_bets';
        $this->players_table = $table_prefix . 'zone_players';
        $this->dreamteams_table = $table_prefix . 'zone_dreamteams';
        $this->match_players_table = $table_prefix . 'zone_match_players';
        $this->match_player_civs_table = $table_prefix . 'zone_match_player_civs';
        $this->maps_table = $table_prefix . 'zone_maps';
        $this->civs_table = $table_prefix . 'zone_civs';
        $this->map_civs_table = $table_prefix . 'zone_map_civs';
        $this->player_map_table = $table_prefix . 'zone_player_map';
        $this->player_civ_table = $table_prefix . 'zone_player_civ';
        $this->matches_table = $table_prefix . 'zone_matches';
        $this->match_teams_table = $table_prefix . 'zone_match_teams';
        $this->match_civs_table = $table_prefix . 'zone_match_civs';
        $this->match_team_civs_table = $table_prefix . 'zone_match_team_civs';
        $this->draw_process_table = $table_prefix . 'zone_draw_process';
        $this->draw_players_table = $table_prefix . 'zone_draw_players';
        $this->user_settings_table = $table_prefix . 'zone_user_settings';
    }

    /**
     * @param array|string $sql
     * @param string $query
     * @return array
     */
    public function get_rows(
        $sql,
        int $limit = 0,
        int $offset = 0,
        $query = 'SELECT'
    ): array {
        $queryString = \is_string($sql) ? rtrim(rtrim($sql), ';') : $this->db->sql_build_query($query, $sql);
        $result = $limit > 0
            ? $this->db->sql_query_limit($queryString, $limit, $offset)
            : $this->db->sql_query($queryString);
        $rows = $this->db->sql_fetchrowset($result);
        $this->db->sql_freeresult($result);
        return $rows ?: [];
    }

    // todo: build this without getting the whole rowset
    public function get_col(
        array $sqlArray,
        int $limit = 0,
        int $offset = 0,
        $query = 'SELECT'
    ): array {
        return \array_map(
            '\reset',
            $this->get_rows($sqlArray, $limit, $offset, $query)
        );
    }

    /**
     * @param array|string $sql
     * @param string $query
     * @return mixed
     */
    public function get_row($sql, $query = 'SELECT')
    {
        $queryString = \is_string($sql)
            ? rtrim(rtrim($sql), ';')
            : $this->db->sql_build_query($query, $sql);
        $result = $this->db->sql_query_limit($queryString, 1);
        $row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);
        return $row;
    }

    /**
     * @param array|string $sql
     * @return mixed|null
     */
    public function get_var($sql)
    {
        // todo: build this without getting the whole row
        $row = $this->get_row($sql);
        return $row ? \reset($row) : null;
    }

    public function insert(string $table, array $data): int
    {
        $this->db->sql_query('INSERT INTO ' . $table . ' ' . $this->db->sql_build_array('INSERT', $data));
        // we cast the insert id to int, ignoring the fact that driver_interface returns strings
        // the id should be a ctype int though, so cast should be alright
        return (int)$this->db->sql_nextid();
    }

    public function update(string $table, array $data, $where): void
    {
        if (\is_array($where)) {
            $whereArray = [];
            foreach ($where as $key => $value) {
                $whereArray[] = $key . ' = ' . $value;
            }
            $where = implode(' AND ', $whereArray);
        }
        $this->db->sql_query('UPDATE ' . $table . ' SET ' . $this->db->sql_build_array('UPDATE', $data) .
            (empty($where) ? '' : ' WHERE ' . $where));
    }

    public function run_txn(callable $func)
    {
        try {
            $this->db->sql_transaction('begin');
            $ret = $func();
            $this->db->sql_transaction('commit');
            return $ret;
        } catch (\Throwable $e) {
            $this->db->sql_transaction('rollback');
            throw $e;
        }
    }

    /** taken from driver_interface */
    public function sql_in_set($field, $array, $negate = false, $allow_empty_set = false)
    {
        return $this->db->sql_in_set($field, $array, $negate, $allow_empty_set);
    }

    /** taken from driver_interface */
    public function sql_query($query = '', $cache_ttl = 0)
    {
        return $this->db->sql_query($query, $cache_ttl);
    }

    /** taken from driver_interface */
    public function sql_multi_insert($table, $sql_ary)
    {
        return $this->db->sql_multi_insert($table, $sql_ary);
    }

    /** taken from driver_interface */
    public function sql_escape($msg)
    {
        return $this->db->sql_escape($msg);
    }
}
