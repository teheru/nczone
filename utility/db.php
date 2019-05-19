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
    /** @var string */
    public $locks_table;

    private const FMT_MAP = [
        '$like' => '%s LIKE %s',
        '$notlike' => '%s NOT LIKE %s',
        '$eq' => ['%s = %s', '%s IS %s'],
        '$ne' => ['%s != %s' , '%s IS NOT %s'],
        '$gt' => '%s > %s',
        '$lt' => '%s < %s',
        '$gte' => '%s >= %s',
        '$lte' => '%s <= %s',
        '$in' => '%s IN (%s)',
        '$nin' => '%s NOT IN (%s)',
    ];

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
        $this->locks_table = $table_prefix . 'zone_locks';
    }

    /**
     * @param array|string $sql
     * @param int $limit
     * @param int $offset
     * @param string $query
     *
     * @return array
     */
    public function get_rows(
        $sql,
        int $limit = 0,
        int $offset = 0,
        $query = 'SELECT'
    ): array {
        $queryString = $this->build_query_string($query, $sql);
        $result = $limit > 0
            ? $this->db->sql_query_limit($queryString, $limit, $offset)
            : $this->db->sql_query($queryString);
        $rows = $this->db->sql_fetchrowset($result);
        $this->db->sql_freeresult($result);
        return $rows ?: [];
    }

    /**
     * @param array|string $sql
     * @param int $limit
     * @param int $offset
     * @param string $query
     *
     * @return int[]
     */
    public function get_int_col(
        $sql,
        int $limit = 0,
        int $offset = 0,
        $query = 'SELECT'
    ): array {
        return \array_map(
            '\intval',
            $this->get_col($sql, $limit, $offset, $query)
        );
    }

    // todo: build this without getting the whole rowset
    public function get_col(
        $sql,
        int $limit = 0,
        int $offset = 0,
        $query = 'SELECT'
    ): array {
        return \array_map(
            '\reset',
            $this->get_rows($sql, $limit, $offset, $query)
        );
    }

    public function build_query_string(string $type, $data): string
    {
        if (\is_string($data)) {
            return \rtrim(\rtrim($data), ';');
        }

        if (!isset($data['SELECT'])) {
            $data['SELECT'] = '*';
        } elseif (\is_array($data['SELECT'])) {
            $data['SELECT'] = $this->build_select($data['SELECT']);
        }

        if (isset($data['WHERE']) && \is_array($data['WHERE'])) {
            $data['WHERE'] = $this->build_where($data['WHERE']);
        }

        return $this->db->sql_build_query($type, $data);
    }

    /**
     * @param array|string $sql
     * @param string $query
     * @return mixed
     */
    public function get_row($sql, $query = 'SELECT')
    {
        $queryString = $this->build_query_string($query, $sql);
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

    public function insert_only(string $table, array $data)
    {
        $dataSql = $this->db->sql_build_array('INSERT', $data);
        return $this->db->sql_query("INSERT INTO {$table} {$dataSql}");
    }

    public function insert(string $table, array $data): int
    {
        $this->insert_only($table, $data);
        // we cast the insert id to int, ignoring the fact that driver_interface returns strings
        // the id should be a ctype int though, so cast should be alright
        return (int)$this->db->sql_nextid();
    }

    public function update(string $table, array $data, $where): void
    {
        $whereSql = \is_array($where) ? $this->build_where($where) : $where;
        $whereSql = $whereSql ? " WHERE {$whereSql}" : '';
        $this->db->sql_query(
            'UPDATE ' . $table
            . ' SET ' . $this->db->sql_build_array('UPDATE', $data) .
            $whereSql);
    }

    public function txn_fn(callable $func)
    {
        return $this->run_txn($func);
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

    private function esc($value)
    {
        if ($value === null) {
            return 'NULL';
        }
        if (\is_string($value)) {
            $v = $this->db->sql_escape($value);
            return "'{$v}'";
        }
        if (\is_float($value) || \is_int($value)) {
            return $value;
        }
        if (\is_bool($value)) {
            return $value ? 1 : 0;
        }
        if (\is_array($value)) {
            return \implode(',', \array_map(function ($v) {
                return $this->esc($v);
            }, $value));
        }
        return $value;
    }

    private static function add_ticks(string $str): string
    {
        return \implode('.', \array_map(function ($part) {
            return \strpos($part, '`') === false ? "`{$part}`" : $part;
        }, \explode('.', $str)));
    }

    private function fmt($fmt, $val, $key): string
    {
        if (\is_array(self::FMT_MAP[$fmt])) {
            $format = self::FMT_MAP[$fmt][$val === null ? 1 : 0];
        } else {
            $format = self::FMT_MAP[$fmt];
        }
        return \sprintf($format, $key, $this->esc($val));
    }

    private function build_select(array $select): string
    {
        $selects = [];
        foreach ($select as $key => $val) {
            $selects[] = \is_int($key) ? $val : "{$key} AS {$val}";
        }
        return \implode(',', $selects);
    }

    private function build_where(array $where): string
    {
        $wheres = [];
        foreach ($where as $key => $val) {
            if (\is_int($key)) {
                $wheres[] = $val;
                continue;
            }

            $k = self::add_ticks($key);

            if (!\is_array($val)) {
                $wheres[] = $this->fmt('$eq', $val, $k);
                continue;
            }

            $any = false;
            foreach (self::FMT_MAP as $from => $tmpl) {
                if (\array_key_exists($from, $val)) {
                    $wheres[] = $this->fmt($from, $val[$from], $k);
                    $any = true;
                }
            }

            if (!$any) {
                $wheres[] = $this->fmt('$in', $val, $k);
            }
        }
        return \implode(' AND ', $wheres);
    }

    public function delete(string $table, array $where): bool
    {
        $whereSql = $this->build_where($where);
        $whereSql = $whereSql ? " WHERE {$whereSql}" : '';
        $this->db->sql_query("DELETE FROM {$table}{$whereSql}");
        return $this->db->sql_affectedrows() !== false;
    }

    public function sql_return_on_error(bool $fail)
    {
        return $this->db->sql_return_on_error($fail);
    }
}
