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

use eru\nczone\config\config;
use eru\nczone\utility\db;
use eru\nczone\utility\number_util;
use eru\nczone\utility\zone_util;

class draw_settings {

    private const MATCH_CIVS = 'match_civs';
    private const PLAYER_CIVS = 'player_civs';
    private const TEAM_CIVS = 'team_civs';

    /** @var db */
    private $db;

    public function __construct(db $db)
    {
        $this->db = $db;
    }

    public function draw_settings(
        int $draw_user_id,
        entity\draw_match $draw_match
    ): entity\draw_setting {
        $match_civ_ids = [];
        $team1_civ_ids = [];
        $team2_civ_ids = [];
        $player_civ_ids = [];
        $map_id = $this->get_players_map_id($draw_match->get_all_player_ids());

        $match_size = $draw_match->get_match_size();

        $civ_kind = $this->decide_draw_civs_kind($draw_match);
        if($civ_kind === self::MATCH_CIVS)
        {
            $extra_civs = config::draw_match_extra_civs($match_size);
            $match_civs = $this->draw_match_civs($map_id, $draw_match, $extra_civs);
            foreach($match_civs as $civ)
            {
                $match_civ_ids[] = (int)$civ['id'];
            }
        }
        elseif($civ_kind === self::TEAM_CIVS)
        {
            $extra_civs = config::draw_team_extra_civs($match_size);
            [$team1_civs, $team2_civs] = $this->draw_teams_civs($map_id, $draw_match, $extra_civs);
            foreach($team1_civs as $civ)
            {
                $team1_civ_ids[] = (int)$civ['id'];
            }
            foreach($team2_civs as $civ)
            {
                $team2_civ_ids[] = (int)$civ['id'];
            }
        }
        else
        {
            $num_civs = config::draw_player_num_civs($match_size);
            $player_civs = $this->draw_player_civs($map_id, $draw_match, $num_civs);
            foreach($player_civs as $user_id => $pc)
            {
                $player_civ_ids[$user_id] = $pc['id'];
            }
        }

        $draw_setting = new entity\draw_setting();
        $draw_setting->set_draw_user_id($draw_user_id);
        $draw_setting->set_map_id($map_id);
        $draw_setting->set_match_civ_ids($match_civ_ids);
        $draw_setting->set_team1_civ_ids($team1_civ_ids);
        $draw_setting->set_team2_civ_ids($team2_civ_ids);
        $draw_setting->set_player_civ_ids($player_civ_ids);
        $draw_setting->set_team1($draw_match->get_team1());
        $draw_setting->set_team2($draw_match->get_team2());
        return $draw_setting;
    }

    /**
     * Calculates the next map (id) to be drawn for a group of players
     *
     * @param array $user_ids
     *
     * @return int
     */
    public function get_players_map_id(array $user_ids): int
    {
        $sql = '
            select
                sq4.map_id,
                sum(sq4.time_diff / sq4.max_time_diff) as value
            from (
                select
                    sq3.user_id,
                    p7.map_id,
                    sq3.max_time_diff,
                    ((
                        select
                            max(p6.time) as max_time
                        from
                            phpbb_zone_player_map p6
                        where p6.user_id = sq3.user_id
                    ) - p7.time) * p8.weight as time_diff
                from (
                    select
                        sq2.user_id,
                        max((sq2.max_time - sq2.time) * sq2.weight) as max_time_diff
                    from (
                        select
                            p1.user_id,
                            p4.time,
                            p5.weight,
                            (
                                select
                                    max(p2.time) as max_time
                                from
                                    phpbb_zone_player_map p2
                                where p2.user_id = p1.user_id
                            ) as max_time
                        from phpbb_zone_player_map p1
                        join phpbb_zone_player_map p4 on p4.user_id = p1.user_id
                        join phpbb_zone_maps p5 on p5.map_id = p4.map_id
                        where '.$this->db->sql_in_set('p1.user_id', $user_ids).'
                    ) sq2
                    group by sq2.user_id
                ) sq3
                join phpbb_zone_player_map p7 on p7.user_id = sq3.user_id
                join phpbb_zone_maps p8 on p8.map_id = p7.map_id
            ) sq4
            group by map_id
            order by value desc;
        ';

        $map_id = $this->db->get_var($sql) ?: 0;

        return (int)$map_id;
    }

    public function decide_draw_civs_kind(entity\draw_match $draw_match): string
    {
        if ($draw_match->get_min_max_diff() >= config::get(config::draw_player_civs)) {
            return self::PLAYER_CIVS;
        }

        if ($draw_match->get_abs_rating_difference() >= config::get(config::draw_team_civs)) {
            return self::TEAM_CIVS;
        }

        return self::MATCH_CIVS;
    }

    protected function draw_players_civs(
        int $map_id,
        array $user_ids,
        int $num_civs,
        int $extra_civs,
        bool $ignore_force = false
    ): array {
        // first, get one of the force draw civs
        $force_civ_num = 0;
        $sql_add = '';
        $test_civ_add = [];
        $force_civ = [];
        if(!$ignore_force)
        {
            $force_civ = $this->db->get_row([
                'SELECT' => 'c.civ_id AS id, c.multiplier AS multiplier',
                'FROM' => [$this->db->map_civs_table => 'c', $this->db->player_civ_table => 'p'],
                'WHERE' => 'c.civ_id = p.civ_id AND c.force_draw AND NOT c.prevent_draw AND c.map_id = ' . $map_id . ' AND ' . $this->db->sql_in_set('p.user_id', $user_ids),
                'GROUP_BY' => 'c.civ_id',
                'ORDER_BY' => 'SUM(' . time() . ' - p.time) DESC',
            ]);
            if($force_civ)
            {
                $force_civ_num = 1;
                $sql_add = ' AND c.civ_id != ' . $force_civ['id'];
                $test_civ_add = [$force_civ];
            }
        }

        // get additional civs
        $draw_civs = $this->db->get_num_rows([
            'SELECT' => 'c.civ_id AS id, c.multiplier AS multiplier',
            'FROM' => [$this->db->map_civs_table => 'c', $this->db->player_civ_table => 'p'],
            'WHERE' => 'c.civ_id = p.civ_id AND NOT c.prevent_draw AND c.map_id = ' . $map_id . ' AND ' . $this->db->sql_in_set('p.user_id', $user_ids) . $sql_add,
            'GROUP_BY' => 'c.civ_id',
            'ORDER_BY' => 'SUM(' . time() . ' - p.time) DESC',
        ], $num_civs + $extra_civs);

        if($extra_civs === 0)
        {
            $best_civs = $draw_civs;
        }
        else
        {
            // we drawed some extra civs and now we drop some to reduce the difference of multipliers
            $best_civs = [];
            $best_value = -1;
            for($i = 0; $i < $extra_civs; $i++)
            {
                $test_civs = \array_slice($draw_civs, $i, $num_civs - $force_civ_num);
                $test_civs_calc = array_merge($test_civ_add, $test_civs);
                $test_civs_calc = civs::sort_by_multiplier($test_civs_calc);
                $value = reset($test_civs_calc)['multiplier'] - end($test_civs_calc)['multiplier'];
                if($value < $best_value || $best_value < 0)
                {
                    $best_civs = $test_civs;
                    $best_value = $value;
                }
            }
        }

        return [$force_civ ?: [], $best_civs];
    }

    public function draw_match_civs(
        int $map_id,
        entity\draw_match $draw_match,
        int $extra_civs = 4
    ): array {
        $players_civs = $this->draw_players_civs(
            $map_id,
            $draw_match->get_all_player_ids(),
            $draw_match->get_match_size(),
            $extra_civs
        );

        $drawed_civs = $players_civs[0]
            ? array_merge([$players_civs[0]], $players_civs[1])
            : $players_civs[1];

        return civs::sort_by_multiplier($drawed_civs);
    }

    public function draw_teams_civs(
        int $map_id,
        entity\draw_match $draw_match,
        int $extra_civs = 2
    ): array {
        $team1_users = $draw_match->get_team1();
        $team2_users = $draw_match->get_team2();

        //$string = "";
        $num_civs = $draw_match->get_match_size();

        $team1_sum_rating = $team1_users->get_total_rating();
        $team2_sum_rating = $team2_users->get_total_rating();

        // find out which civs have to be in both teams
        $both_teams_civs = zone_util::maps()->get_map_both_teams_civ_ids($map_id);

        // we use some extra civs to be able to draw fair civs for the teams
        [$team1_force_civ, $team1_civpool] = $this->draw_players_civs($map_id, $team1_users->get_ids(), $num_civs + $extra_civs, 0);

        // if the force civ must be in both teams, we don't want to draw another one for team 2
        $ignore_force = False;
        if($team1_force_civ)
        {
            $num_civs--;

            if(\in_array($team1_force_civ['id'], $both_teams_civs))
            {
                $ignore_force = True;
            }
        }

        $team2_players_civs = $this->draw_players_civs($map_id, $team2_users->get_ids(), $num_civs + $extra_civs, 0, $ignore_force);
        $team2_force_civ = $team2_players_civs[0];
        if($ignore_force)
        {
            $team2_force_civ = $team1_force_civ;
        }
        $team2_civpool = $team2_players_civs[1];

        // create a common civpool for civs which has to be in both teams
        $both_civpool = [];
        foreach($team1_civpool as $key => $civ)
        {
            if(\in_array($civ['id'], $both_teams_civs))
            {
                $both_civpool[] = $civ;
                unset($team1_civpool[$key]);
            }
        }
        foreach($team2_civpool as $key => $civ)
        {
            if(\in_array($civ['id'], $both_teams_civs))
            {
                if (!\in_array($civ['id'], $both_civpool))   $both_civpool[] = $civ;
                unset($team2_civpool[$key]);
            }
        }

        [$team1_civpool, $team2_civpool] = $this->remove_extra_civs_from_civpools($team1_civpool, $team2_civpool);

        $unique_civpool_num = \count($team1_civpool);

        $test_indices = $this->get_civpool_test_indices($num_civs, $extra_civs);

        $team1_best_indices = [];
        $team2_best_indices = [];
        $best_value = -1;

        // we test all possible combinations and take the combination, which minimizes |diff(player_ratings * multipliers)|
        foreach($test_indices as $team1_indices)
        {
            $both_civs_indices_team_1 = [];
            $team1_sum_multiplier = 0;
            foreach($team1_indices as $index)
            {
                if($index < $unique_civpool_num)
                {
                    $team1_sum_multiplier += $team1_civpool[$index]['multiplier'];
                }
                else
                {
                    $team1_sum_multiplier += $both_civpool[$index - $unique_civpool_num]['multiplier'];
                    $both_civs_indices_team_1[] = $index;
                }
            }
            if($team1_force_civ)
            {
                $team1_sum_multiplier += $team1_force_civ['multiplier'];
            }

            foreach($test_indices as $team2_indices)
            {
                $team2_sum_multiplier = 0;
                $both_civs_indices_team_2 = [];
                foreach($team2_indices as $index)
                {
                    if($index < $unique_civpool_num)
                    {
                        $team2_sum_multiplier += $team2_civpool[$index]['multiplier'];
                    }
                    else
                    {
                        $team2_sum_multiplier += $both_civpool[$index - $unique_civpool_num]['multiplier'];
                        $both_civs_indices_team_2[] = $index;
                    }
                }
                sort($both_civs_indices_team_1);
                sort($both_civs_indices_team_2);
                if($both_civs_indices_team_1 != $both_civs_indices_team_2)
                {
                        continue 1;
                }

                if($team2_force_civ)
                {
                    $team2_sum_multiplier += $team2_force_civ['multiplier'];
                }

                $diff = number_util::diff(
                    $team1_sum_rating * $team1_sum_multiplier,
                    $team2_sum_rating * $team2_sum_multiplier
                );
                if($diff < $best_value || $best_value < 0)
                {
                    $team1_best_indices = $team1_indices;
                    $team2_best_indices = $team2_indices;
                    $best_value = $diff;
                }
            }
        }


        // apply the indices on the civpools
        $team1_civs = [];
        $team2_civs = [];
        foreach($team1_best_indices as $index)
        {
            $team1_civs[] = $index < $unique_civpool_num
                ? $team1_civpool[$index]
                : $both_civpool[$index - $unique_civpool_num];
        }
        foreach($team2_best_indices as $index)
        {
            $team2_civs[] = $index < $unique_civpool_num
                ? $team2_civpool[$index]
                : $both_civpool[$index - $unique_civpool_num];
        }

        // and add the civs forced by the map
        if($team1_force_civ)
        {
            $team1_civs[] = $team1_force_civ;
        }
        if($team2_force_civ)
        {
            $team2_civs[] = $team2_force_civ;
        }

        return [
            civs::sort_by_multiplier($team1_civs),
            civs::sort_by_multiplier($team2_civs),
        ];
    }

    public function draw_player_civs(
        int $map_id,
        entity\draw_match $draw_match,
        int $num_civs = 3
    ): array {
        $team1 = $draw_match->get_team1();
        $team2 = $draw_match->get_team2();

        $civs_module = zone_util::civs();

        // find out which civs have to be in both teams
        $both_teams_civ_ids = zone_util::maps()->get_map_both_teams_civ_ids($map_id);

        $user_civpools = [];

        foreach ($team1->items() as $user) {
            $user_civpools[$user->get_id()] = [];
        }

        foreach ($team2->items() as $user) {
            $user_civpools[$user->get_id()] = [];
        }

        // get civs which are forced for the map
        $force_civ_ids = [];

        $team1_force_civ = $civs_module->get_map_players_single_civ($map_id, $team1->get_ids(), [], False, True);
        if($team1_force_civ)
        {
            // check if the civ has to be in both teams anyways
            if(\in_array($team1_force_civ['civ_id'], $both_teams_civ_ids))
            {
                $team2_force_civ = $civs_module->get_map_players_single_civ($map_id, $team2->get_ids(), [$team1_force_civ['civ_id']], False, True);
            }
            else
            {
                // check force civ for the other team except the one drawed before
                $team2_force_civ = $civs_module->get_map_players_single_civ($map_id, $team2->get_ids(), [$team1_force_civ['civ_id']], True, True);
                if($team2_force_civ)
                {
                    // check again if THIS civ has to be in both teams
                    if(\in_array($team2_force_civ['civ_id'], $both_teams_civ_ids))
                    {
                        $team1_force_civ = $civs_module->get_map_players_single_civ($map_id, $team1->get_ids(), [$team2_force_civ['civ_id']], False, True);
                    }
                }
                // if this didn't work, we only have one forced civ and it has to be in both teams nevertheless
                else
                {
                    $team2_force_civ = $civs_module->get_map_players_single_civ($map_id, $team2->get_ids(), [$team1_force_civ['civ_id']], False, True);
                }
            }

            // we need to remember these civs so we don't drop them or draw them again
            $force_civ_ids[] = $team1_force_civ['civ_id'];
            if($team1_force_civ['civ_id'] != $team2_force_civ['civ_id'])
            {
                $force_civ_ids[] = $team2_force_civ['civ_id'];
            }

            $user_civpools[$team1_force_civ['user_id']][] = [
                'id' => $team1_force_civ['civ_id'],
                'multiplier' => $team1_force_civ['multiplier'],
            ];
            $user_civpools[$team2_force_civ['user_id']][] = [
                'id' => $team2_force_civ['civ_id'],
                'multiplier' => $team2_force_civ['multiplier'],
            ];
        }

        $user_civs = $civs_module->get_map_players_multiple_civs(
            $map_id,
            $draw_match->get_all_player_ids(),
            $force_civ_ids
        );

        // civs we don't want to drop
        $keep_civ_ids = array_merge($force_civ_ids, $both_teams_civ_ids);

        // remember civs that were already drawed so we don't draw them twice
        $civpool_civs = [];
        foreach($user_civs as $pc)
        {
            if(\in_array($pc['civ_id'], $civpool_civs)) {
                continue;
            }

            if (\count($user_civpools[$pc['user_id']]) >= $num_civs) {
                continue;
            }

            foreach ($user_civpools[$pc['user_id']] as $civ) {
                // we don't want to have multiple civs with same multiplier for the same player
                // also, don't add civs for players which have a civ which we want to keep
                if ($pc['multiplier'] == $civ['multiplier'] || \in_array($civ['id'], $keep_civ_ids)) {
                    continue 2;
                }
            }

            if (\in_array($pc['civ_id'], $both_teams_civ_ids)) {
                // if the drawed civ should be in both teams, get the player from the other team who should get that civ
                $other_team = $civs_module->get_map_players_single_civ(
                    $map_id,
                    $team1->contains_id($pc['user_id']) ? $team2->get_ids() : $team1->get_ids(),
                    [$pc['civ_id']],
                    false,
                    false
                );
                if (\count($user_civpools[$other_team['user_id']]) > 0) {
                    foreach ($user_civpools[$other_team['user_id']] as $civ) {
                        // if he already has a forced civ or a civ for both teams, we drop the recently drawed civ
                        if (\in_array($civ['id'], $keep_civ_ids)) {
                            continue 2;
                        }
                    }
                }

                // overwrite the other players civs
                foreach ($user_civpools[$other_team['user_id']] as $civ) {
                    unset($civpool_civs[array_search($civ['id'], $civpool_civs)]);
                }
                $user_civpools[$other_team['user_id']] = [[
                    'id' => $other_team['civ_id'],
                    'multiplier' => $other_team['multiplier'],
                ]];
            }

            $civpool_civs[] = $pc['civ_id'];
            if (\in_array($pc['civ_id'], $both_teams_civ_ids)) {
                // overwrite this players civs
                foreach ($user_civpools[$pc['user_id']] as $civ) {
                    unset($civpool_civs[array_search($civ['id'], $civpool_civs)]);
                }
                $user_civpools[$pc['user_id']] = [[
                    'id' => $pc['civ_id'],
                    'multiplier' => $pc['multiplier'],
                ]];
            }
            else
            {
                $user_civpools[$pc['user_id']][] = [
                    'id' => $pc['civ_id'],
                    'multiplier' => $pc['multiplier'],
                ];
            }
        }

        return $this->get_best_user_civ_combination($user_civpools, $team1, $team2);
    }

    // calculate sum rating * multiplier and minimize the abs difference for the teams
    private function get_best_user_civ_combination(array $user_civpools, entity\match_players_list $team1, entity\match_players_list $team2): array
    {
        $best_civ_combination = [];
        $best_diff = -1;
        foreach ($this->get_user_civ_combinations($user_civpools, array_merge($team1->get_ids(), $team2->get_ids())) as $cc) {
            $diff = number_util::diff(
                $team1->get_total_rating_with_multiplier_map($cc),
                $team2->get_total_rating_with_multiplier_map($cc)
            );
            if ($diff < $best_diff || $best_diff < 0) {
                $best_civ_combination = $cc;
                $best_diff = $diff;
            }
        }
        return $best_civ_combination;
    }

    // calculate all possible combinations of civs for the players
    private function get_user_civ_combinations(array $user_civpools, array $user_ids): array
    {
        $civ_combinations = [];
        $first_user_id = array_shift($user_ids);
        foreach ($user_civpools[$first_user_id] as $civ) {
            $civ_combinations[] = [$first_user_id => $civ];
        }

        foreach ($user_ids as $user_id) {
            $new_civ_combinations = [];
            foreach ($civ_combinations as $cc) {
                foreach ($user_civpools[$user_id] as $civ) {
                    $temp = $cc;
                    $temp[$user_id] = $civ;
                    $new_civ_combinations[] = $temp;
                }
            }
            $civ_combinations = $new_civ_combinations;
        }
        return $civ_combinations;
    }

    // get all index combinations with length $num_civs for our civpools
    private function get_civpool_test_indices(int $num_civs, int $extra_civs): array
    {
        $test_indices = [];
        for ($i = 0; $i < $extra_civs + 1; $i++) {
            $test_indices[] = [$i];
        }
        for ($i = 1; $i < $num_civs; $i++) {
            $temp = [];
            foreach ($test_indices as $lo) {
                for ($j = end($lo) + 1; $j < $num_civs + $extra_civs; $j++) {
                    $temp[] = array_merge($lo, [$j]);
                }
            }
            $test_indices = $temp;
        }
        return $test_indices;
    }


    // remove extra civs from either team civpools
    private function remove_extra_civs_from_civpools(array $team1_civpool, array $team2_civpool): array
    {
        $diff_num = \count($team1_civpool) - \count($team2_civpool);
        if ($diff_num > 0) {
            for ($i = 0; $i < $diff_num; $i++) {
                array_pop($team1_civpool);
            }
        } else {
            for ($i = 0; $i < -$diff_num; $i++) {
                array_pop($team2_civpool);
            }
        }
        return [$team1_civpool, $team2_civpool];
    }
}
