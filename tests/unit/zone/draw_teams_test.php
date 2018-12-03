<?php

namespace eru\nczone\tests\zone;

use eru\nczone\zone\draw_teams;
use eru\nczone\zone\entity\match_player;
use eru\nczone\zone\entity\match_players_list;
use PHPUnit\Framework\TestCase;

class draw_teams_test extends TestCase
{
    /**
     * @dataProvider make_matches_provider
     */
    public function test_make_matches($list, $factor, $expected_teams)
    {
        $dt = new draw_teams();
        self::assertEquals($expected_teams, $dt->make_matches($list, $factor));
    }

    public function make_matches_provider()
    {
        $nili = new match_player(3, 3417);
        $chaos_2_win = new match_player(4, 3084);
        $nc_tonic = new match_player(5, 2431);
        $havoc = new match_player(6, 2102);
        $teutonic_tanks = new match_player(7, 3078);
        $nc_philipphjs = new match_player(8, 2917);
        $fwb2 = new match_player(9, 2764);
        $ann0r = new match_player(10, 2407);
        $hooked_on_a_feeling = new match_player(11, 679);
        $omurchu = new match_player(12, 650);
        $samedo_sama = new match_player(13, 205);
        $kalaran = new match_player(14, 608);
        $trennig = new match_player(15, 498);
        $lion = new match_player(16, 463);
        $lion_copy = new match_player(17, 463);

        return [
            '2_players' => [
                $list = match_players_list::from_match_players([
                    $nili,
                    $kalaran,
                ]),
                $factor = 0.4,
                $expected_teams = [
                    [
                        match_players_list::from_match_players([$nili]),
                        match_players_list::from_match_players([$kalaran]),
                    ]
                ]
            ],
            '2_players_reverse' => [
                $list = match_players_list::from_match_players([
                    $kalaran,
                    $nili,
                ]),
                $factor = 0.4,
                $expected_teams = [
                    [
                        match_players_list::from_match_players([$nili]),
                        match_players_list::from_match_players([$kalaran]),
                    ],
                ],
            ],
            '2_players_same' => [
                $list = match_players_list::from_match_players([
                    $lion_copy,
                    $lion,
                ]),
                $factor = 0.4,
                $expected_teams = [
                    [
                        match_players_list::from_match_players([$lion_copy]),
                        match_players_list::from_match_players([$lion]),
                    ]
                ]
            ],
            '2_players_same_reverse' => [
                $list = match_players_list::from_match_players([
                    $lion,
                    $lion_copy,
                ]),
                $factor = 0.4,
                $expected_teams = [
                    [
                        match_players_list::from_match_players([$lion]),
                        match_players_list::from_match_players([$lion_copy]),
                    ],
                ],
            ],
            '8_players' => [
                $list = match_players_list::from_match_players([
                    $nc_philipphjs,
                    $nc_tonic,
                    $ann0r,
                    $havoc,
                    $nili,
                    $teutonic_tanks,
                    $chaos_2_win,
                    $fwb2,
                ]),
                $factor = 0.4,
                $expected_teams = [
                    [
                        match_players_list::from_match_players([
                            $nili,
                            $chaos_2_win,
                            $nc_tonic,
                            $havoc,
                        ]),
                        match_players_list::from_match_players([
                            $teutonic_tanks,
                            $nc_philipphjs,
                            $fwb2,
                            $ann0r,
                        ]),
                    ],
                ],
            ],
            '6_players' => [
                $list = match_players_list::from_match_players([
                    $lion,
                    $trennig,
                    $samedo_sama,
                    $hooked_on_a_feeling,
                    $kalaran,
                    $omurchu,
                ]),
                $factor = 0.4,
                $expected_teams = [
                    [
                        match_players_list::from_match_players([
                            $hooked_on_a_feeling,
                            $omurchu,
                            $samedo_sama,
                        ]),
                        match_players_list::from_match_players([
                            $kalaran,
                            $trennig,
                            $lion,
                        ]),
                    ],
                ],
            ],
            '14_players' => [
                $list = match_players_list::from_match_players([
                    $ann0r,
                    $chaos_2_win,
                    $fwb2,
                    $havoc,
                    $hooked_on_a_feeling,
                    $kalaran,
                    $lion,
                    $nili,
                    $nc_philipphjs,
                    $nc_tonic,
                    $omurchu,
                    $samedo_sama,
                    $teutonic_tanks,
                    $trennig,
                ]),
                $factor = 0.4,
                $expected_teams = [
                    [
                        match_players_list::from_match_players([
                            $nili,
                            $chaos_2_win,
                            $nc_tonic,
                            $havoc,
                        ]),
                        match_players_list::from_match_players([
                            $teutonic_tanks,
                            $nc_philipphjs,
                            $fwb2,
                            $ann0r,
                        ]),
                    ],
                    [
                        match_players_list::from_match_players([
                            $hooked_on_a_feeling,
                            $omurchu,
                            $samedo_sama,
                        ]),
                        match_players_list::from_match_players([
                            $kalaran,
                            $trennig,
                            $lion,
                        ]),
                    ],
                ],
            ],
            '13_players' => [
                $list = match_players_list::from_match_players([
                    $ann0r,
                    $chaos_2_win,
                    $fwb2,
                    $havoc,
                    $kalaran,
                    $lion,
                    $nili,
                    $nc_philipphjs,
                    $nc_tonic,
                    $omurchu,
                    $samedo_sama,
                    $teutonic_tanks,
                    $trennig, // :( last player in list will be cut off
                ]),
                $factor = 0.4,
                $expected_teams = [
                    [
                        match_players_list::from_match_players([
                            $nili,
                            $teutonic_tanks,
                            $nc_tonic,
                        ]),
                        match_players_list::from_match_players([
                            $chaos_2_win,
                            $nc_philipphjs,
                            $fwb2,
                        ]),
                    ],
                    [
                        match_players_list::from_match_players([
                            $ann0r,
                            $kalaran,
                            $samedo_sama,
                        ]),
                        match_players_list::from_match_players([
                            $havoc,
                            $omurchu,
                            $lion,
                        ]),
                    ],
                ],
            ],
        ];
    }
}
