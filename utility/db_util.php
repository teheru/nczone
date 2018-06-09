<?php

namespace eru\nczone\utility;

use phpbb\db\driver\driver_interface;

class db_util
{
    public static function get_rows(driver_interface $db, array $sqlArray, $query = 'SELECT'): array
    {
        $result = $db->sql_query($db->sql_build_query($query, $sqlArray));
        $rows = $db->sql_fetchrowset($result);
        $db->sql_freeresult($result);
        return $rows ?: [];
    }

    // todo: build this without getting the whole rowset
    public static function get_col(driver_interface $db, array $sqlArray, $query = 'SELECT'): array
    {
        $col = [];
        foreach (self::get_rows($db, $sqlArray, $query) as $row) {
            $col[] = reset($row);
        }
        return $col;
    }

    public static function get_row(driver_interface $db, array $sqlArray, $query = 'SELECT')
    {
        $result = $db->sql_query($db->sql_build_query($query, $sqlArray));
        $row = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        return $row;
    }

    // todo: build this without getting the whole row
    public static function get_var(driver_interface $db, array $sqlArray)
    {
        $row = self::get_row($db, $sqlArray);
        return $row ? reset($row) : null;
    }

    public static function insert(driver_interface $db, string $table, array $data): string
    {
        $db->sql_query('INSERT INTO ' . $table . ' ' . $db->sql_build_array('INSERT', $data));
        return (string)$db->sql_nextid();
    }

    public static function update(driver_interface $db, string $table, array $sqlArray, array $whereArray): void
    {
        $where = [];
        foreach ($whereArray as $key => $value) {
            $where[] = $key . ' = ' . $value;
        }
        $db->sql_query('UPDATE ' . $table . ' SET ' . $db->sql_build_array('UPDATE', $sqlArray) .
            (empty($where) ? '' : ' WHERE ' . implode(' AND ', $where)));
    }
}
