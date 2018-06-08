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

class civs {
	// TODO: attributes

	public function __construct(\phpbb\db\driver\driver_interface $db,
	$civs_table, $maps_table, $map_civs_table)
	{
		$this->db = $db;

		$this->civs_table = $civs_table;
		$this->maps_table = $maps_table;
		$this->map_civs_table = $map_civs_table;
	}

	public function get_civs()
	{
		$sql_array = array(
			'SELECT' => 'c.civ_id AS id, c.civ_name AS name',
			'FROM' => array($this->civs_table => 'c')
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);

		$result = $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	public function get_civ($civ_id)
	{
		$civ_id = (int) $civ_id;

		$sql_array = array(
			'SELECT' => 'c.civ_name AS name',
			'FROM' => array($this->civs_table => 'c'),
			'WHERE' => 'c.civ_id = ' . $civ_id
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);

		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row;
	}

	public function create_civ($civ_name)
	{
		$sql_array = array(
			'civ_id' => 0,
			'civ_name' => $civ_name
		);
		$this->db->sql_query('INSERT INTO ' . $this->civs_table . ' ' . $this->db->sql_build_array('INSERT', $sql_array));
		$civ_id = $this->db->sql_nextid();

		$sql_array = array(
			'SELECT' => 'm.map_id AS map_id',
			'FROM' => array($this->maps_table => 'm')
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		foreach($rows as $row)
		{
			$sql_array = array(
				'map_id' => $row['map_id'],
				'civ_id' => $civ_id,
				'multiplier' => 0.0,
				'force_draw' => false,
				'prevent_draw' => false,
				'both_teams' => false,
			);
			$this->db->sql_query('INSERT INTO ' . $this->map_civs_table . ' ' . $this->db->sql_build_array('INSERT', $sql_array));
		}

		return $civ_id;
	}

	public function edit_civ($civ_id, $civ_info)
	{
		$civ_id = (int) $civ_id;

		$civ_data = array(
			'civ_name' => $civ_info['name']
		);
		$this->db->sql_query('UPDATE ' . $this->civs_table . ' SET ' . $this->db->sql_build_array('UPDATE', $civ_data) . ' WHERE civ_id = ' . $civ_id);
	}
}

?>
