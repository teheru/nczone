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

/**
 * Class to make teams (not maps or civs!)
 */
class draw_teams
{
    // These are number of players, which doesn't work with the scheme in get_match_sizes()
    // so these are taken instead
    /** @var array */
    private static $special_match_sizes = [
        0 => [],
        2 => [1 => 1],
        4 => [2 => 1],
        6 => [3 => 1],
        10 => [2 => 1, 3 => 1],
        12 => [3 => 2],
        18 => [3 => 3],
    ];

    // all combinations of players in two teams which makes sense for 3vs3
    /** @var array */
    private static $teams_3vs3 = [
        [[0, 3, 4], [1, 2, 5]],
        [[0, 2, 5], [1, 3, 4]],
        [[0, 3, 5], [1, 2, 4]],
        [[0, 1, 5], [2, 3, 4]],
        [[0, 4, 5], [1, 2, 3]]
    ];

    // same thing for 4vs4
    /** @var array */
    private static $teams_4vs4 = [
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

    /**
     * Calculates the best sizes of matches. We avoid 1vs1 and 2vs2 if possible and prefer 4vs4 over 3vs3.
     *
     * @param int    $num_players    Number of players do be drawn
     *
     * @return array
     */
    public static function get_match_sizes(int $num_players): array
    {
        $even_num_players = $num_players - ($num_players % 2);

        if (array_key_exists($even_num_players, self::$special_match_sizes)) {
            return self::$special_match_sizes[$even_num_players];
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
     * @param array $match_sizes The number of match sizes n vs n (n => number)
     *
     * @return array
     */
    public static function permute_match_sizes(array $match_sizes): array
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

    /**
     * Calculates the best teams for a single match.
     *
     * @param match_players_list $player_list List of the players (must have index 'rating') to be put in teams
     *
     * @return array
     */
    public function make_match(match_players_list $match_players_list): array
    {
        $player_list = $match_players_list->sorted_by_rating()->items();

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

            foreach (self::$teams_3vs3 as $teams) {
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

            foreach (self::$teams_4vs4 as $teams) {
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

    /**
     * Divides players in usable groups to make teams and than calculate the optimal teams.
     *
     * @param match_players_list $player_list List of the players (must have index 'rating') to be put in teams
     *
     * @return array
     */
    public function make_matches(match_players_list $player_list): array
    {
        if ($player_list->length() % 2 === 1) {
            $player_list->pop();
        }

        $sorted_player_list = $player_list->sorted_by_rating();

        $match_sizes = self::get_match_sizes($sorted_player_list->length());
        $permutes = self::permute_match_sizes($match_sizes);
        $best_value = -1;
        $best_teams = [];

        foreach ($permutes as $permute) {
            $curr_value = 0;
            $curr_teams = [];

            $offset = 0;
            foreach ($permute as $match_size) {
                $result = $this->make_match($sorted_player_list->slice($offset, $match_size * 2));
                $offset += $match_size * 2;
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
}
