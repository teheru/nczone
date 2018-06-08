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

class draw {
	// TODO: attributes
	
	public function __construct()
	{
		$this->special_match_sizes = array(
			0 => array(),
			2 => array(1 => 1),
			4 => array(2 => 1),
			6 => array(3 => 1),
			10 => array(2 => 1, 3 => 1),
			12 => array(3 => 2),
			18 => array(3 => 3),
		);

		$this->teams_3vs3 = array(array(array(0,3,4),array(1,2,5)),array(array(0,2,5),array(1,3,4)),array(array(0,3,5),array(1,2,4)),array(array(0,1,5),array(2,3,4)),array(array(0,4,5),array(1,2,3)));
		$this->teams_4vs4 = array(array(array(0,3,4,7),array(1,2,5,6)),array(array(0,3,5,6),array(1,2,4,7)),array(array(0,2,5,7),array(1,3,4,6)),array(array(0,1,6,7),array(2,3,4,5)),array(array(0,3,4,6),array(1,2,5,7)),array(array(0,2,5,6),array(1,3,4,7)),array(array(0,2,6,7),array(1,3,4,5)),array(array(0,1,5,7),array(2,3,4,6)),array(array(0,4,5,6),array(1,2,3,7)),array(array(0,3,4,5),array(1,2,6,7)),array(array(0,1,5,6),array(2,3,4,7)),array(array(0,3,5,7),array(1,2,4,6)),array(array(0,2,4,7),array(1,3,5,6)),array(array(0,3,6,7),array(1,2,4,5)),array(array(0,4,5,7),array(1,2,3,6)),array(array(0,1,4,7),array(2,3,5,6)),array(array(0,2,3,7),array(1,4,5,6)),array(array(0,5,6,7),array(1,2,3,4)),array(array(0,1,2,7),array(3,4,5,6)),array(array(0,1,3,7),array(2,4,5,6)),array(array(0,4,6,7),array(1,2,3,5)));
	}

	public function get_match_sizes($num_players)
	{
		$num_players = $num_players - ($num_players % 2);

		if(array_key_exists($num_players, $this->special_match_sizes))
		{
			return $this->special_match_sizes[$num_players];
		}
		elseif(($num_players % 8) == 0)
		{
			return array(4 => $num_players / 8);
		}
		elseif((($num_players - 6) % 8) == 0)
		{
			return array(3 => 1, 4 => ($num_players - 6)/8);
		}
		elseif((($num_players - 12) % 8) == 0)
		{
			return array(3 => 2, 4 => ($num_players - 12)/8);
		}
		else
		{
			return array(3 => 3, 4 => ($num_players - 18)/8);
		}
	}

	public function permute_match_sizes($match_sizes)
	{
		if(count($match_sizes) == 1)
		{
			$match_size = key($match_sizes);
			$number = $match_sizes[$match_size];
			return array(array_fill(0, $number, $match_size));
		}
		elseif(in_array(1, $match_sizes))
		{
			$single_match_size = array_search(1, $match_sizes);
			unset($match_sizes[$single_match_size]);

			$other_match_size = key($match_sizes);
			$number = $match_sizes[$other_match_size];

			$permutes = array();
			for($i = 0; $i < $number + 1; $i++)
			{
				$permute = array_fill(0, $number + 1, $other_match_size);
				$permute[$i] = $single_match_size;
				$permutes[] = $permute;
			}
			return $permutes;
		}
		elseif(in_array(2, $match_sizes))
		{
			$double_match_size = array_search(2, $match_sizes);
			unset($match_sizes[$double_match_size]);

			$other_match_size = key($match_sizes);
			$number = $match_sizes[$other_match_size];

			$permutes = array();
			for($i = 0; $i < $number + 2; $i++)
			{
				for($j = 0; $j < $i; $j++)
				{
					$permute = array_fill(0, $number + 2, $other_match_size);
					$permute[$i] = $double_match_size;
					$permute[$j] = $double_match_size;
					$permutes[] = $permute;
				}
			}
			return $permutes;
		}
		else
		{
			$triple_match_size = array_search(3, $match_sizes);
			unset($match_sizes[$triple_match_size]);

			$other_match_size = key($match_sizes);
			$number = $match_sizes[$other_match_size];

			$permutes = array();
			for($i = 0; $i < $number + 3; $i++)
			{
				for($j = 0; $j < $i; $j++)
				{
					for($k = 0; $k < $j; $k++)
					{
						$permute = array_fill(0, $number + 3, $other_match_size);
						$permute[$i] = $triple_match_size;
						$permute[$j] = $triple_match_size;
						$permute[$k] = $triple_match_size;
						$permutes[] = $permute;
					}
				}
			}
			return $permutes;
		}
	}

	public function make_match($player_list)
	{
		usort($player_list, array($this, 'cmp_ratings'));

		$num_players = count($player_list);
		if($num_players == 2)
		{
			return array(
				'teams' => array(array($player_list[0]), array($player_list[1])),
				'value' => $player_list[0]['rating'] - $player_list[1]['rating']
			);
		}
		elseif($num_players == 4)
		{
			return array(
				'teams' => array(
					array($player_list[0], $player_list[3]),
					array($player_list[1], $player_list[2])
				),
				'value' => abs($player_list[0]['rating'] + $player_list[3]['rating'] -
											 $player_list[1]['rating'] - $player_list[2]['rating'])
			);
		}
		elseif($num_players == 6)
		{
			$best_value = -1.0;
			$best_teams = array();

			foreach($this->teams_3vs3 as $teams)
			{
				$team1 = array(
					$player_list[$teams[0][0]],
					$player_list[$teams[0][1]],
					$player_list[$teams[0][2]]
				);
				$team2 = array(
					$player_list[$teams[1][0]],
					$player_list[$teams[1][1]],
					$player_list[$teams[1][2]]
				);
				$value = abs($team1[0]['rating'] + $team1[1]['rating'] +
										 $team1[2]['rating'] - $team2[0]['rating'] -
										 $team2[1]['rating'] - $team2[2]['rating']);
				if($best_value < 0.0 || $value < $best_value)
				{
					$best_value = $value;
					$best_teams = array($team1, $team2);
				}
			}
			return array('teams' => $best_teams, 'value' => $best_value);
		}
		elseif($num_players == 8)
		{
			$best_value = -1.0;
			$best_teams = array();

			foreach($this->teams_4vs4 as $teams)
			{
				$team1 = array(
					$player_list[$teams[0][0]],
					$player_list[$teams[0][1]],
					$player_list[$teams[0][2]],
					$player_list[$teams[0][3]]
				);
				$team2 = array(
					$player_list[$teams[1][0]],
					$player_list[$teams[1][1]],
					$player_list[$teams[1][2]],
					$player_list[$teams[1][3]]
				);
				$value = abs($team1[0]['rating'] + $team1[1]['rating'] +
					  		 $team1[2]['rating'] + $team1[3]['rating'] -
							 $team2[0]['rating'] - $team2[1]['rating'] -
							 $team2[2]['rating'] - $team2[3]['rating']);
				if($best_value < 0.0 || $value < $best_value)
				{
					$best_value = $value;
					$best_teams = array($team1, $team2);
				}
			}
			return array('teams' => $best_teams, 'value' => $best_value);
		}
	}

	public function make_matches($player_list)
	{
		$num_players = count($player_list);
		if($num_players % 2 == 1)
		{
			array_pop($player_list);
			$num_players--;
		}
		usort($player_list, array($this, 'cmp_ratings'));

		$match_sizes = $this->get_match_sizes($num_players);
		$permutes = $this->permute_match_sizes($match_sizes);

		$best_value = -1;
		$best_teams = array();
		foreach($permutes as $permute)
		{
			$curr_value = 0;
			$curr_teams = array();

			$offset = 0;
			foreach($permute as $match_size)
			{
				$temp_player_list = array_slice($player_list, $offset, $match_size * 2);
				$offset += $match_size * 2;

				$result = $this->make_match($temp_player_list);
				$curr_value += $result['value'];
				$curr_teams[] = $result['teams'];
			}

			if($best_value < 0.0 or $curr_value < $best_value)
			{
				$best_value = $curr_value;
				$best_teams = $curr_teams;
			}
		}
		return $best_teams;
	}

	static function cmp_ratings($p1, $p2)
	{
		$p1_rating = $p1['rating'];
		$p2_rating = $p2['rating'];
		if($p1_rating == $p2_rating)
		{
			return 0;
		}
		return ($p1_rating < $p2_rating) ? 1 : -1;
	}
}

?>
