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

class maps {
	// TODO: attributes
	
	public function __construct(\phpbb\db\driver\driver_interface $db, \eru\nczone\zone\civs $zone_civs,
	$maps_table, $map_civs_table)
	{
		$this->db = $db;

		$this->zone_civs = $zone_civs;

		$this->maps_table = $maps_table;
		$this->map_civs_table = $map_civs_table;
	}

	public function get_maps()
	{
		$sql_array = array(
			'SELECT' => 'm.map_id AS id, m.map_name AS name, m.weight AS weight',
			'FROM' => array($this->maps_table => 'm')
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);

		$result = $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	public function get_map($map_id)
	{
		$map_id = (int) $map_id;

		$sql_array = array(
			'SELECT' => 'm.map_name AS name, m.weight AS weight',
			'FROM' => array($this->maps_table => 'm'),
			'WHERE' => 'm.map_id = ' . $map_id
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);

		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row;
	}

	public function get_map_civs($map_id)
	{
		$map_id = (int) $map_id;

		$sql_array = array(
			'SELECT' => 'c.civ_id AS civ_id, c.multiplier AS multiplier, c.force_draw AS force_draw, c.prevent_draw AS prevent_draw, c.both_teams AS both_teams',
			'FROM' => array($this->map_civs_table => 'c'),
			'WHERE' => 'c.map_id = ' . $map_id
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);

		$result = $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	public function create_map($map_name, $weight, $copy_map_id=0)
	{
		$copy_map_id = (int) $copy_map_id;
		$weight = (float) $weight;

		$sql_array = array(
			'map_id' => 0,
			'map_name' => $map_name,
			'weight' => $weight
		);
		$this->db->sql_query('INSERT INTO ' . $this->maps_table . ' ' . $this->db->sql_build_array('INSERT', $sql_array));
		$map_id = $this->db->sql_nextid();

		$sql_array = array();
		if($copy_map_id)
		{
			$map_civs = $this->get_map_civs($copy_map_id);
			foreach($map_civs as $map_civ)
			{
				$map_civ['map_id'] = $map_id;
				$sql_array = $map_civ;
			}
		}
		else
		{
			$civs = $this->zone_civs->get_civs();
			foreach($civs as $civ)
			{
				$sql_array[] = array(
					'map_id' => $map_id,
					'civ_id' => (int) $civ['id'],
					'multiplier' => 0.0,
					'force_draw' => false,
					'prevent_draw' => false,
					'both_teams' => false
				);
			}
		}
		$this->db->sql_multi_insert($this->map_civs_table, $sql_array);

		return $map_id;
	}

	public function edit_map($map_id, $map_info)
	{
		$map_id = (int) $map_id;

		$sql_array = array();
		if(array_key_exists('name', $map_info))
		{
			$sql_array['map_name'] = $map_info['name'];
		}
		if(array_key_exists('weight', $map_info))
		{
			$sql_array['weight'] = (float) $map_info['weight'];
		}

		$this->db->sql_query('UPDATE ' . $this->maps_table . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_array) . ' WHERE map_id = ' . $map_id);
	}

	public function edit_map_civs($map_id, $map_civs)
	{
		$map_id = (int) $map_id;

		foreach($map_civs as $civ_id => $map_civ)
		{
			$civ_id = (int) $civ_id;
			$sql_array = array(
				'multiplier' => (float) $map_civ['multiplier'],
				'force_draw' => (bool) $map_civ['force_draw'],
				'prevent_draw' => (bool) $map_civ['prevent_draw'],
				'both_teams' => (bool) $map_civ['both_teams']
			);
			$this->db->sql_query('UPDATE '. $this->map_civs_table . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_array) . ' WHERE map_id = ' . $map_id . ' AND civ_id = ' . $civ_id);
		}
	}
}

?>
