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

use eru\nczone\utility\db;
use eru\nczone\utility\number_util;
use eru\nczone\utility\zone_util;
use eru\nczone\utility\phpbb_util;

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

    public function draw_settings(array $team1, array $team2): array
    {
        $config = phpbb_util::config();

        $players = array_merge($team1, $team2);

        $match_civ_ids = [];
        $team1_civ_ids = [];
        $team2_civ_ids = [];
        $player_civ_ids = [];
        $map_id = $this->get_players_map_id($players);

        $match_size = \count($team1);
        $vs_str = $match_size . 'vs' . $match_size;

        $civ_kind = $this->decide_draw_civs_kind($team1, $team2);
        if($civ_kind === self::MATCH_CIVS)
        {
            $match_civs = $this->draw_match_civs($map_id, $players, 0, $config['nczone_draw_match_extra_civs_' . $vs_str]);
            foreach($match_civs as $civ)
            {
                $match_civ_ids[] = $civ['id'];
            }
        }
        elseif($civ_kind === self::TEAM_CIVS)
        {
            [$team1_civs, $team2_civs] = $this->draw_teams_civs($map_id, $team1, $team2, 0, $config['nczone_draw_team_extra_civs_' . $vs_str]);
            foreach($team1_civs as $civ)
            {
                $team1_civ_ids[] = $civ['id'];
            }
            foreach($team2_civs as $civ)
            {
                $team2_civ_ids[] = $civ['id'];
            }
        }
        else
        {
            $player_civs = $this->draw_player_civs($map_id, $team1, $team2, $config['nczone_draw_player_num_civs_' . $vs_str]);
            foreach($player_civs as $user_id => $pc)
            {
                $player_civ_ids[$user_id] = $pc['id'];
            }
        }

        return [$map_id, $match_civ_ids, $team1_civ_ids, $team2_civ_ids, $player_civ_ids];
    }

    /**
     * Calculates the next map (id) to be drawn for a group of players
     *
     * @param array  $users
     *
     * @return int
     */
    public function get_players_map_id(array $users): int
    {
        $user_ids = [];
        foreach($users as $user)
        {
            $user_ids[] = $user['id'];
        }
        $user_num = \count($user_ids);

        $rows = $this->db->get_rows([
            'SELECT' => 't.map_id, SUM(' . time() . ' - t.time) * m.weight AS val',
            'FROM' => [$this->db->player_map_table => 't', $this->db->maps_table => 'm'],
            'WHERE' => 't.map_id = m.map_id AND ' . $this->db->sql_in_set('t.user_id', $user_ids),
            'GROUP_BY' => 't.map_id, t.user_id',
            'ORDER_BY' => 'val DESC'
        ]);

        $counter = [];
        foreach($rows as $r)
        {
            if(!array_key_exists($r['map_id'], $counter))
            {
                $counter[$r['map_id']] = 1;
            }
            else
            {
                $counter[$r['map_id']]++;
            }

            if($counter[$r['map_id']] === $user_num)
            {
                return (int)$r['map_id'];
            }
        }

        return 0;
    }

    public function decide_draw_civs_kind(array $team1_users, array $team2_users): string
    {
        $config = phpbb_util::config();

        $min_max_diff = number_util::diff(
            players::get_max_rating($team1_users, $team2_users),
            players::get_min_rating($team1_users, $team2_users)
        );

        if ($min_max_diff >= $config['nczone_draw_player_civs']) {
            return self::PLAYER_CIVS;
        }

        $rating_sum_diff = number_util::diff(
            players::get_rating_sum($team1_users),
            players::get_rating_sum($team2_users)
        );

        if($rating_sum_diff >= $config['nczone_draw_team_civs']) {
            return self::TEAM_CIVS;
        }

        return self::MATCH_CIVS;
    }

    protected function draw_players_civs(int $map_id, array $users, int $num_civs, int $extra_civs, bool $ignore_force=False): array
    {
        $civ_ids = [];

        $user_ids = [];
        foreach($users as $user)
        {
            $user_ids[] = $user['id'];
        }

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
        ], $num_civs - \count($civ_ids) + $extra_civs);

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
                $value = $test_civs_calc[0]['multiplier'] - end($test_civs_calc)['multiplier'];
                reset($test_civs_calc);
                if($value < $best_value || $best_value < 0)
                {
                    $best_civs = $test_civs;
                    $best_value = $value;
                }
            }
        }

        return [$force_civ ?: NULL, $best_civs];
    }

    public function draw_match_civs(int $map_id, array $users, int $num_civs=0, int $extra_civs=4): array
    {
        $players_civs = $this->draw_players_civs($map_id, $users, $num_civs ?: \count($users) / 2, $extra_civs);

        $drawed_civs = $players_civs[0]
            ? array_merge([$players_civs[0]], $players_civs[1])
            : $players_civs[1];

        return civs::sort_by_multiplier($drawed_civs);
    }

    public function draw_teams_civs(int $map_id, array $team1_users, array $team2_users, int $num_civs=0, int $extra_civs=2): array
    {
        if($num_civs === 0)
        {
            $num_civs = \count($team1_users);
        }


        $team1_sum_rating = players::get_rating_sum($team1_users);
        $team2_sum_rating = players::get_rating_sum($team2_users);


        // find out which civs have to be in both teams
        $both_teams_civs = zone_util::maps()->get_map_both_teams_civ_ids($map_id);


        // we use some extra civs to be able to draw fair civs for the teams
        $team1_players_civs = $this->draw_players_civs($map_id, $team1_users, $num_civs + $extra_civs, 0);
        $team1_force_civ = $team1_players_civs[0];
        $team1_civpool = $team1_players_civs[1];

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

        $team2_players_civs = $this->draw_players_civs($map_id, $team2_users, $num_civs + $extra_civs, 0, $ignore_force);
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
                $both_civpool[] = $civ;
                unset($team2_civpool[$key]);
            }
        }

        // remove extra civs from either team civpools
        $diff_num = \count($team1_civpool) - \count($team2_civpool);
        if($diff_num > 0)
        {
            for($i = 0; $i < $diff_num; $i++)
            {
                array_pop($team1_civpool);
            }
        }
        else
        {
            for($i = 0; $i < -$diff_num; $i++)
            {
                array_pop($team2_civpool);
            }
        }
        $unique_civpool_num = \count($team1_civpool);


        // get all index combinations with length $num_civs for our civpools
        $test_indices = [];
        for($i = 0; $i < $extra_civs + 1; $i++)
        {
            $test_indices[] = [$i];
        }
        for($i = 1; $i < $num_civs; $i++)
        {
            $temp = [];
            foreach($test_indices as $lo)
            {
                for($j = end($lo)+1; $j < $num_civs + $extra_civs; $j++)
                {
                    $temp[] = array_merge($lo, [$j]);
                }
            }
            $test_indices = $temp;
        }


        $team1_best_indices = [];
        $team2_best_indices = [];
        $best_value = -1;

        // we test all possible combinations and take the combination, which minimizes |diff(player_ratings * multipliers)|
        foreach($test_indices as $team1_indices)
        {
            $both_civs_indices = [];

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
                    $both_civs_indices[] = $index;
                }
            }
            if($team1_force_civ)
            {
                $team1_sum_multiplier += $team1_force_civ['multiplier'];
            }

            foreach($test_indices as $team2_indices)
            {
                foreach($both_civs_indices as $index)
                {
                    if(!\in_array($index, $team2_indices))
                    {
                        continue 2;
                    }
                }

                $team2_sum_multiplier = 0;
                foreach($team2_indices as $index)
                {
                    if($index < $unique_civpool_num)
                    {
                        $team2_sum_multiplier += $team2_civpool[$index]['multiplier'];
                    }
                    else
                    {
                        $team2_sum_multiplier += $both_civpool[$index - $unique_civpool_num]['multiplier'];
                    }
                }
                if($team2_force_civ)
                {
                    $team2_sum_multiplier += $team2_force_civ['multiplier'];
                }

                $value = number_util::diff(
                    $team1_sum_rating * $team1_sum_multiplier,
                    $team2_sum_rating * $team2_sum_multiplier
                );
                if($value < $best_value || $best_value < 0)
                {
                    $team1_best_indices = $team1_indices;
                    $team2_best_indices = $team2_indices;
                    $best_value = $value;
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

    public function draw_player_civs(int $map_id, array $team1_users, array $team2_users, int $num_civs=3): array
    {
        $civs_module = zone_util::civs();

        // find out which civs have to be in both teams
        $both_teams_civ_ids = zone_util::maps()->get_map_both_teams_civ_ids($map_id);

        $user_ids = [];
        $team1_ids = [];
        $team2_ids = [];
        $user_ratings = [];
        $user_civpools = [];
        foreach($team1_users as $user)
        {
            $user_ids[] = $user['id'];
            $team1_ids[] = $user['id'];
            $user_ratings[$user['id']] = $user['rating'];
            $user_civpools[$user['id']] = [];
        }
        foreach($team2_users as $user)
        {
            $user_ids[] = $user['id'];
            $team2_ids[] = $user['id'];
            $user_ratings[$user['id']] = $user['rating'];
            $user_civpools[$user['id']] = [];
        }


        // get civs which are forced for the map
        $force_civ_ids = [];

        $team1_force_civ = $civs_module->get_map_players_single_civ($map_id, $team1_ids, [], False, True);
        if($team1_force_civ)
        {
            // check if the civ has to be in both teams anyways
            if(\in_array($team1_force_civ['civ_id'], $both_teams_civ_ids))
            {
                $team2_force_civ = $civs_module->get_map_players_single_civ($map_id, $team2_ids, [$team1_force_civ['civ_id']], False, True);
            }
            else
            {
                // check force civ for the other team except the one drawed before
                $team2_force_civ = $civs_module->get_map_players_single_civ($map_id, $team2_ids, [$team1_force_civ['civ_id']], True, True);
                if($team2_force_civ)
                {
                    // check again if THIS civ has to be in both teams
                    if(\in_array($team2_force_civ['civ_id'], $both_teams_civ_ids))
                    {
                        $team1_force_civ = $civs_module->get_map_players_single_civ($map_id, $team1_ids, [$team2_force_civ['civ_id']], False, True);
                    }
                }
                // if this didn't work, we only have one forced civ and it has to be in both teams nevertheless
                else
                {
                    $team2_force_civ = $civs_module->get_map_players_single_civ($map_id, $team2_ids, [$team1_force_civ['civ_id']], False, True);
                }
            }

            // we need to remember these civs so we don't drop them or draw them again
            $force_civ_ids[] = $team1_force_civ['civ_id'];
            if($team1_force_civ['civ_id'] != $team2_force_civ['civ_id'])
            {
                $force_civ_ids[] = $team2_force_civ['civ_id'];
            }

            $user_civpools[$team1_force_civ['user_id']][] = ['id' => $team1_force_civ['civ_id'], 'multiplier' => $team1_force_civ['multiplier']];
            $user_civpools[$team2_force_civ['user_id']][] = ['id' => $team2_force_civ['civ_id'], 'multiplier' => $team2_force_civ['multiplier']];
        }
        // civs we don't want to drop
        $keep_civ_ids = array_merge($force_civ_ids, $both_teams_civ_ids);


        $user_civs = $civs_module->get_map_players_multiple_civs($map_id, $user_ids, $force_civ_ids, True, False);

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
                $other_team = $civs_module->get_map_players_single_civ($map_id, (\in_array($pc['user_id'], $team1_ids)) ? $team2_ids : $team1_ids, [$pc['civ_id']], False, False);
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
                $user_civpools[$other_team['user_id']] = [['id' => $other_team['civ_id'], 'multiplier' => $other_team['multiplier']]];
            }

            $civpool_civs[] = $pc['civ_id'];
            if (\in_array($pc['civ_id'], $both_teams_civ_ids)) {
                // overwrite this players civs
                foreach ($user_civpools[$pc['user_id']] as $civ) {
                    unset($civpool_civs[array_search($civ['id'], $civpool_civs)]);
                }
                $user_civpools[$pc['user_id']] = [['id' => $pc['civ_id'], 'multiplier' => $pc['multiplier']]];
            }
            else
            {
                $user_civpools[$pc['user_id']][] = ['id' => $pc['civ_id'], 'multiplier' => $pc['multiplier']];
            }
        }

        // calculate all possible combinations of civs for the players
        $civ_combinations = [];
        $first_user_id = array_shift($user_ids);
        foreach($user_civpools[$first_user_id] as $civ)
        {
            $civ_combinations[] = [$first_user_id => $civ];
        }

        foreach($user_ids as $user_id)
        {
            $new_civ_combinations = [];
            foreach($civ_combinations as $cc)
            {
                foreach($user_civpools[$user_id] as $civ)
                {
                    $temp = $cc;
                    $temp[$user_id] = $civ;
                    $new_civ_combinations[] = $temp;
                }
            }
            $civ_combinations = $new_civ_combinations;
        }


        // calculate sum rating * multiplier and minimize the abs difference for the teams
        $best_civ_combination = [];
        $best_diff = -1;
        foreach($civ_combinations as $cc)
        {
            $team1_sum = 0;
            $team2_sum = 0;
            foreach($cc as $user_id => $civ)
            {
                if(\in_array($user_id, $team1_ids))
                {
                    $team1_sum += $user_ratings[$user_id] * $civ['multiplier'];
                }
                else
                {
                    $team2_sum += $user_ratings[$user_id] * $civ['multiplier'];
                }
            }
            $diff = number_util::diff($team1_sum, $team2_sum);
            if($diff < $best_diff || $best_diff < 0)
            {
                $best_civ_combination = $cc;
                $best_diff = $diff;
            }
        }

        return $best_civ_combination;
    }
}
