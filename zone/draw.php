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

use eru\nczone\utility\number_util;
/**
 * Class to make teams (not maps or civs!)
 */
class draw
{
    /** @var array */
    private $special_match_sizes;
    /** @var array */
    private $teams_3vs3;
    /** @var array */
    private $teams_4vs4;

    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->special_match_sizes = [
            0 => [],
            2 => [1 => 1],
            4 => [2 => 1],
            6 => [3 => 1],
            10 => [2 => 1, 3 => 1],
            12 => [3 => 2],
            18 => [3 => 3],
        ];

        $this->teams_3vs3 = [
            [[0, 3, 4], [1, 2, 5]],
            [[0, 2, 5], [1, 3, 4]],
            [[0, 3, 5], [1, 2, 4]],
            [[0, 1, 5], [2, 3, 4]],
            [[0, 4, 5], [1, 2, 3]]
        ];

        $this->teams_4vs4 = [
            [[0, 3, 4, 7], [1, 2, 5, 6]],
            [[0, 3, 5, 6], [1, 2, 4, 7]],
            [[0, 2, 5, 7], [1, 3, 4, 6]],
            [[0, 1, 6, 7], [2, 3, 4, 5]],
            [[0, 3, 4, 6], [1, 2, 5, 7]],
            [[0, 2, 5, 6], [1, 3, 4, 7]],
            [[0, 2, 6, 7], [1, 3, 4, 5]],
            [[0, 1, 5, 7], [2, 3, 4, 6]],
            [[0, 4, 5, 6], [1, 2, 3, 7]],
            [[0, 3, 4, 5], [1, 2, 6, 7]],
            [[0, 1, 5, 6], [2, 3, 4, 7]],
            [[0, 3, 5, 7], [1, 2, 4, 6]],
            [[0, 2, 4, 7], [1, 3, 5, 6]],
            [[0, 3, 6, 7], [1, 2, 4, 5]],
            [[0, 4, 5, 7], [1, 2, 3, 6]],
            [[0, 1, 4, 7], [2, 3, 5, 6]],
            [[0, 2, 3, 7], [1, 4, 5, 6]],
            [[0, 5, 6, 7], [1, 2, 3, 4]],
            [[0, 1, 2, 7], [3, 4, 5, 6]],
            [[0, 1, 3, 7], [2, 4, 5, 6]],
            [[0, 4, 6, 7], [1, 2, 3, 5]]
        ];
    }


    /**
     * Calculates the best sizes of matches.
     * 
     * @param int    $num_players    Number of players do be drawn
     * 
     * @return array
     */
    public function get_match_sizes(int $num_players): array
    {
        $even_num_players = $num_players - ($num_players % 2);

        if (array_key_exists($even_num_players, $this->special_match_sizes)) {
            return $this->special_match_sizes[$even_num_players];
        }

        if (($even_num_players % 8) === 0) {
            return [4 => $even_num_players / 8];
        }

        if ((($even_num_players - 6) % 8) === 0) {
            return [3 => 1, 4 => ($even_num_players - 6) / 8];
        }

        if ((($even_num_players - 12) % 8) === 0) {
            return [3 => 2, 4 => ($even_num_players - 12) / 8];
        }

        return [3 => 3, 4 => ($even_num_players - 18) / 8];
    }


    /**
     * Permutes match sizes in all possible combinations without redundancy.
     * 
     * @param array    $match_sizes    The number of match sizes n vs n (n => number)
     * 
     * @return array
     */
    public function permute_match_sizes(array $match_sizes): array
    {
        if (\count($match_sizes) === 1) {
            $match_size = key($match_sizes);
            $number = $match_sizes[$match_size];
            return [array_fill(0, $number, $match_size)];
        }

        if (\in_array(1, $match_sizes, true)) {
            $single_match_size = array_search(1, $match_sizes, true);
            unset($match_sizes[$single_match_size]);

            $other_match_size = key($match_sizes);
            $number = $match_sizes[$other_match_size];

            $permutes = [];
            for ($i = 0; $i < $number + 1; $i++) {
                $permute = array_fill(0, $number + 1, $other_match_size);
                $permute[$i] = $single_match_size;
                $permutes[] = $permute;
            }
            return $permutes;
        }

        if (\in_array(2, $match_sizes, true)) {
            $double_match_size = array_search(2, $match_sizes, true);
            unset($match_sizes[$double_match_size]);

            $other_match_size = key($match_sizes);
            $number = $match_sizes[$other_match_size];

            $permutes = [];
            for ($i = 0; $i < $number + 2; $i++) {
                for ($j = 0; $j < $i; $j++) {
                    $permute = array_fill(0, $number + 2, $other_match_size);
                    $permute[$i] = $double_match_size;
                    $permute[$j] = $double_match_size;
                    $permutes[] = $permute;
                }
            }
            return $permutes;
        }

        $triple_match_size = array_search(3, $match_sizes, true);
        unset($match_sizes[$triple_match_size]);

        $other_match_size = key($match_sizes);
        $number = $match_sizes[$other_match_size];

        $permutes = [];
        for ($i = 0; $i < $number + 3; $i++) {
            for ($j = 0; $j < $i; $j++) {
                for ($k = 0; $k < $j; $k++) {
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

    public function make_match(array $player_list): array
    {
        usort($player_list, [__CLASS__, 'cmp_ratings']);

        $num_players = \count($player_list);
        if ($num_players === 2) {
            return [
                'teams' => [[$player_list[0]], [$player_list[1]]],
                'value' => $player_list[0]['rating'] - $player_list[1]['rating']
            ];
        }

        if ($num_players === 4) {
            return [
                'teams' => [
                    [$player_list[0], $player_list[3]],
                    [$player_list[1], $player_list[2]]
                ],
                'value' => abs($player_list[0]['rating'] + $player_list[3]['rating'] -
                    $player_list[1]['rating'] - $player_list[2]['rating'])
            ];
        }

        if ($num_players === 6) {
            $best_value = -1.0;
            $best_teams = [];

            foreach ($this->teams_3vs3 as $teams) {
                $team1 = [
                    $player_list[$teams[0][0]],
                    $player_list[$teams[0][1]],
                    $player_list[$teams[0][2]]
                ];
                $team2 = [
                    $player_list[$teams[1][0]],
                    $player_list[$teams[1][1]],
                    $player_list[$teams[1][2]]
                ];
                $value = abs($team1[0]['rating'] + $team1[1]['rating'] +
                    $team1[2]['rating'] - $team2[0]['rating'] -
                    $team2[1]['rating'] - $team2[2]['rating']);
                if ($best_value < 0.0 || $value < $best_value) {
                    $best_value = $value;
                    $best_teams = [$team1, $team2];
                }
            }
            return ['teams' => $best_teams, 'value' => $best_value];
        }

        if ($num_players === 8) {
            $best_value = -1.0;
            $best_teams = [];

            foreach ($this->teams_4vs4 as $teams) {
                $team1 = [
                    $player_list[$teams[0][0]],
                    $player_list[$teams[0][1]],
                    $player_list[$teams[0][2]],
                    $player_list[$teams[0][3]]
                ];
                $team2 = [
                    $player_list[$teams[1][0]],
                    $player_list[$teams[1][1]],
                    $player_list[$teams[1][2]],
                    $player_list[$teams[1][3]]
                ];
                $value = abs($team1[0]['rating'] + $team1[1]['rating'] +
                    $team1[2]['rating'] + $team1[3]['rating'] -
                    $team2[0]['rating'] - $team2[1]['rating'] -
                    $team2[2]['rating'] - $team2[3]['rating']);
                if ($best_value < 0.0 || $value < $best_value) {
                    $best_value = $value;
                    $best_teams = [$team1, $team2];
                }
            }
            return ['teams' => $best_teams, 'value' => $best_value];
        }

        # todo: maybe throw exception for invalid num_players?
        return ['teams' => [], 'value' => 0];
    }

    public function make_matches(array $player_list): array
    {
        $num_players = \count($player_list);
        if ($num_players % 2 === 1) {
            array_pop($player_list);
            $num_players--;
        }
        usort($player_list, [__CLASS__, 'cmp_ratings']);

        $match_sizes = $this->get_match_sizes($num_players);
        $permutes = $this->permute_match_sizes($match_sizes);
        $best_value = -1;
        $best_teams = [];
        foreach ($permutes as $permute) {
            $curr_value = 0;
            $curr_teams = [];

            $offset = 0;
            foreach ($permute as $match_size) {
                $temp_player_list = \array_slice($player_list, $offset, $match_size * 2);
                $offset += $match_size * 2;

                $result = $this->make_match($temp_player_list);
                $curr_value += $result['value'];
                $curr_teams[] = $result['teams'];
            }

            if ($best_value < 0.0 || $curr_value < $best_value) {
                $best_value = $curr_value;
                $best_teams = $curr_teams;
            }
        }
        return $best_teams;
    }

    public static function cmp_ratings($p1, $p2): int
    {
        return number_util::cmp($p1['rating'], $p2['rating']);
    }
}
