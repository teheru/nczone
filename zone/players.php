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

class players {
	// TODO: attributes

	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\user $user,
	$players_table, $users_table)
	{
		$this->db = $db;
		$this->user = $user;

		$this->players_table = $players_table;
		$this->users_table = $users_table;
	}

	public function get_player($user_id)
	{
		$user_id = (int) $user_id;

		$sql_array = array(
			'SELECT' => 'p.logged_in AS logged_in, p.rating AS rating, p.matches_won AS matches_won, p.matches_loss AS matches_loss',
			'FROM' => array($this->players_table => 'p'),
			'WHERE' => 'p.user_id = '. $user_id
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult();

		if(!$row)
		{
			$row = array();
		}

		$sql_array = array(
			'SELECT' => 'u.username AS username',
			'FROM' => array($this->users_table => 'u'),
			'WHERE' => 'u.user_id = ' . $user_id
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		$row = array_merge($row, $this->db->sql_fetchrow($result));
		$this->db->sql_freeresult();

		return $row;
	}

	public function activate_player($user_id, $rating)
	{
		$user_id = (int) $user_id;
		$rating = (int) $rating;

		$result = $this->db->sql_query('SELECT COUNT(*) AS n FROM ' . $this->players_table . ' WHERE user_id = '. $user_id);
		$row = (int) $this->db->sql_fetchfield('n');
		$this->db->sql_freeresult($result);

		if($row == 0)
		{
			$sql_array = array(
				'user_id' => $user_id,
				'rating' => $rating,
				'logged_in' => 0,
				'matches_won' => 0,
				'matches_loss' => 0
			);
			$this->db->sql_query('INSERT INTO ' . $this->players_table . ' ' . $this->db->sql_build_array('INSERT', $sql_array));

			return true;
		}
		else
		{
			return false;
		}
	}

	public function edit_player($user_id, $player_info)
	{
		$user_id = (int) $user_id;

		$sql_array = array();
		if(array_key_exists('rating', $player_info))
		{
			$sql_array['rating'] = (int) $player_info['rating'];
		}
		if(array_key_exists('logged_in', $player_info))
		{
			$sql_array['logged_in'] = (int) $player_info['logged_in'];
		}
		if(array_key_exists('matches_won', $player_info))
		{
			$sql_array['matches_won'] = (int) $player_info['matches_won'];
		}
		if(array_key_exists('matches_loss', $player_info))
		{
			$sql_array['matches_loss'] = (int) $player_info['matches_loss'];
		}

		$this->db->sql_query('UPDATE '. $this->players_table . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_array) . ' WHERE user_id = '. $user_id);
	}

	public function login_player($user_id)
	{
		$this->edit_player($user_id, array('logged_in' => time()));
	}

	public function logout_player($user_id)
	{
		$this->edit_player($user_id, array('logged_in' => 0));
	}

	public function get_logged_in()
	{
		$sql_array = array(
			'SELECT' => 'p.user_id AS user_id, u.username AS username, p.rating AS rating, p.logged_in AS logged_in',
			'FROM' => array($this->players_table => 'p', $this->users_table => 'u'),
			'WHERE' => 'logged_in > 0 AND p.user_id = u.user_id',
			'ORDER_BY' => 'logged_in DESC'
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	public function get_players_map_id($user_ids)
	{
		$sql_array = array(
			'SELECT' => 't.map_id',
			'FROM' => array('zone_player_map_time' => 't', 'zone_maps' => 'm'),
			'WHERE' => 't.map_id = m.map_id AND '. $db->sql_in_set('user_id', $user_ids),
			'GROUP_BY' => 't.map_id',
			'ORDER_BY' => 'SUM(UNIX_TIMESTAMP() - t.time) / m.weight DESC'
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_fetchfield('map_id');
		$this->db->sql_freeresult($result);

		return $result;
	}
}

?>
