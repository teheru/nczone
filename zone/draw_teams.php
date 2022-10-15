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
use eru\nczone\zone\entity;

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
        2 => [],
        4 => [],
        6 => [3 => 1],
        10 => [4 => 1],
        12 => [3 => 2],
        18 => [3 => 3],
    ];

    // all combinations of players in two teams which makes sense for specific number of players
    /** @var array */
    private static $teams_def = [
        // 2 players
        2 => [
            [[0], [1]],
        ],
        // 4 players
        4 => [
            [[0, 3], [1, 2]],
        ],
        // 6 players
        6 => [
            [[0, 3, 4], [1, 2, 5]],
            [[0, 2, 5], [1, 3, 4]],
            [[0, 3, 5], [1, 2, 4]],
            [[0, 1, 5], [2, 3, 4]],
            [[0, 4, 5], [1, 2, 3]],
        ],
        // 8 players
        8 => [
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
            [[0, 4, 6, 7], [1, 2, 3, 5]],
        ],
    ];

    // all possible permutations when switching lowest two players of one drawing with top two players of another
    /** @var array */
    private static $permute_players_def = [
        [[0, 1], [2, 3]],
        [[0, 2], [1, 3]],
        [[0, 3], [1, 2]],
        [[1, 2], [0, 3]],
        [[1, 3], [0, 2]],
        [[2, 3], [0, 1]],
    ];

    /**
     * Calculates the best sizes of matches. We avoid 1vs1 and 2vs2 if possible and prefer 4vs4 over 3vs3.
     *
     * @param int $num_players Number of players do be drawn
     *
     * @return array
     */
    private static function get_match_sizes(int $num_players): array
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
    private static function permute_match_sizes(array $match_sizes): array
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

    public function switch_players_indices(int $number_matches): array {
        if ($number_matches == 1) {
            return [[0]];
        }

        if ($number_matches == 2) {
            return [[1, 1]];
        }

        $even_indices = array_fill(0, $number_matches, 0);
        $odd_indices = array_fill(0, $number_matches, 0);

        $j = 1;
        for ($i = 0; $i < $number_matches - 1; $i += 2) {
            $even_indices[$i] = $j;
            $even_indices[$i + 1] = $j;
            $j++;
        }

        $j = 1;
        for ($i = 1; $i < $number_matches - 1; $i += 2) {
            $odd_indices[$i] = $j;
            $odd_indices[$i + 1] = $j;
            $j++;
        }

        return [$even_indices, $odd_indices];
    }

    public function permute_edge_players(
        entity\match_players_list $upper_players,
        entity\match_players_list $lower_players,
        int $number
    ): array {
        if ($number == 0) {
            return [[$upper_players, $lower_players]];
        }

        if ($number == 1) {
            $default = [$upper_players, $lower_players];
            $upper_players = clone $upper_players;
            $lower_players = clone $lower_players;

            $upper_player = $upper_players->pop();
            $lower_player = $lower_players->shift();
            $upper_players->push($lower_player);
            $lower_players->unshift($upper_player);
            $switched = [$upper_players, $lower_players];

            return [$default, $switched];
        }

        if ($number == 2) {
            $switched_player_permutations = [];

            $upper_players = clone $upper_players;
            $lower_players = clone $lower_players;

            $p2 = $upper_players->pop();
            $p1 = $upper_players->pop();
            $p3 = $lower_players->shift();
            $p4 = $lower_players->shift();

            $players_in_question = [
                $p1,
                $p2,
                $p3,
                $p4
            ];

            foreach (self::$permute_players_def as $permutations) {
                $temp_upper_players = clone $upper_players;
                $temp_lower_players = clone $lower_players;

                $upper_indices = $permutations[0];
                $lower_indices = $permutations[1];
                $temp_upper_players->push(
                    $players_in_question[$upper_indices[0]],
                    $players_in_question[$upper_indices[1]]
                );
                $temp_lower_players->unshift(
                    $players_in_question[$lower_indices[0]],
                    $players_in_question[$lower_indices[1]]
                );

                $switched_player_permutations[] = [$temp_upper_players, $temp_lower_players];
            }

            return $switched_player_permutations;
        }

        throw new \InvalidArgumentException('$number may only be one of 0, 1 or 2');
    }

    public function make_match(
        entity\match_players_list $player_list,
        float $factor
    ): entity\draw_match {
        [$match] = $this->make_matches($player_list, $factor, 0, 0);
        return $match;
    }

    public function permute_player_lists(
        int $number_matches,
        array $match_players,
        int $number_player_permutations
    ): array {
        $matches_switch = self::switch_players_indices($number_matches);

        $final_player_lists = [];

        foreach ($matches_switch as $match_switch) {
            $player_lists_block_lists = [];
            $player_list_save = null;
            for ($i = 0; $i < $number_matches; $i++) {
                $player_list = $match_players[$i];
                if ($match_switch[$i] == 0) {
                    $player_lists_block_lists[] = [[$player_list]];
                } elseif (!$player_list_save) {
                    $player_list_save = $player_list;
                } else {
                    $permuted_player_lists = self::permute_edge_players(
                        $player_list_save,
                        $player_list,
                        $number_player_permutations
                    );
                    $player_lists_block_lists[] = $permuted_player_lists;

                    $player_list_save = null;
                }
            }

            $build_player_lists = [];
            foreach ($player_lists_block_lists as $player_lists_block) {
                if (!$build_player_lists) {
                    $build_player_lists = $player_lists_block;
                    continue;
                }

                $temp_build_player_lists = $build_player_lists;
                $build_player_lists = [];
                foreach ($temp_build_player_lists as $temp_build_player_list) {
                    foreach ($player_lists_block as $player_lists) {
                        $single_player_list = array_merge($temp_build_player_list, $player_lists);
                        $build_player_lists[] = $single_player_list;
                    }
                }
            }
            $final_player_lists = array_merge($final_player_lists, $build_player_lists);
        }

        return $final_player_lists;
    }

    public function switch_player_number(int $number_matches, int $switch_0_players, int $switch_1_players): int {
        if ($number_matches >= $switch_0_players) {
            return 0;
        }

        if ($number_matches >= $switch_1_players) {
            return 1;
        }

        return 2;
    }

    /**
     * Divides players in usable groups to make teams and than calculate the optimal teams.
     *
     * @param entity\match_players_list $player_list List of the players (must have index 'rating') to be put in teams
     * @param $factor
     * @param $switch_0_players Number of matches to stop switching players
     * @param $switch_1_player Number of matches to switch only one player
     *
     * @return entity\draw_match[]
     */
    public function make_matches(
        entity\match_players_list $player_list,
        float $factor,
        int $switch_0_players,
        int $switch_1_player
    ): array {
        if ($player_list->length() % 2 === 1) {
            $player_list->pop();
        }

        $sorted_player_list = $player_list->sorted_by_rating();

        $match_sizes = self::get_match_sizes($sorted_player_list->length());
        $match_permutes = self::permute_match_sizes($match_sizes);
        $number_matches = array_sum($match_sizes);

        $number_player_permutations = self::switch_player_number(
            $number_matches,
            $switch_0_players,
            $switch_1_player
        );

        $best_value = -1;
        $best_draw_matches = [];

        foreach ($match_permutes as $match_permute) {
            $offset = 0;
            $match_players = [];
            foreach ($match_permute as $match_size) {
                $match_players[] = $sorted_player_list->slice($offset, $match_size * 2);
                $offset += $match_size * 2;
            }

            $permuted_player_lists = self::permute_player_lists(
                $number_matches,
                $match_players,
                $number_player_permutations
            );

            foreach ($permuted_player_lists as $player_lists) {
                $curr_rating_diff = 0;
                $curr_variance = 0;
                $curr_draw_matches = [];

                foreach ($player_lists as $player_list) {
                    $draw_match = $this->_make_match($player_list);
                    $curr_rating_diff += $draw_match->get_abs_rating_difference();
                    $curr_draw_matches[] = $draw_match;
                    $curr_variance += entity\match_players_list::get_abs_rating_variance($player_list);
                }

                if ($factor < 0.0 || $factor > 1.0) {
                    $factor = 0.0;
                }
                $curr_value = $factor * $curr_variance + (1.0 - $factor) * $curr_rating_diff;
                if ($best_value < 0.0 || $curr_value < $best_value) {
                    $best_value = $curr_value;
                    $best_draw_matches = $curr_draw_matches;
                }
            }
        }
        return $best_draw_matches;
    }

    /**
     * Calculates the best teams for a single match.
     * Note that the $players list is already sorted by rating through make_matches function
     *
     * @param entity\match_players_list $players List of the players to be put in teams.
     *
     * @return entity\draw_match
     */
    private function _make_match(
        entity\match_players_list $players
    ): entity\draw_match {
        return $this->make_match_with_team_def(
            self::$teams_def[$players->length()] ?? [],
            $players
        );
    }

    private function make_match_with_team_def(
        array $team_def,
        entity\match_players_list $players
    ): entity\draw_match {
        if (empty($team_def)) {
            throw new \InvalidArgumentException(
                'team definition may not be empty'
            );
        }

        /** @var entity\draw_match $best */
        $best = null;

        foreach ($team_def as $teams) {
            $current = new entity\draw_match(
                $players->pick_indexes(...$teams[0]),
                $players->pick_indexes(...$teams[1])
            );
            if ($best === null || $current->get_abs_rating_difference() < $best->get_abs_rating_difference()) {
                $best = $current;
            }
        }

        return $best;
    }
}
