<?php

namespace eru\nczone\tests\zone;

use eru\nczone\zone\draw_teams;
use eru\nczone\zone\entity\match_player;
use eru\nczone\zone\entity\match_players_list;
use PHPUnit\Framework\TestCase;

class draw_teams_test extends TestCase
{
    /**
     * @dataProvider make_matches_data_provider
     */
    public function test_make_matches($list, $factor, $expected_teams)
    {
        $dt = new draw_teams();
        self::assertEquals($expected_teams, $dt->make_matches($list, $factor));
    }

    public function make_matches_data_provider()
    {
        $additiveConst = 1000; //a constant that is added from each rating for each test set
        $iterations = 2;
        $test_players = array();
        $j = 0;
        for ($i=0; $i < $iterations; $i++) {
            $test_players['nili'.$i] = new match_player(3+$j, 3417+$i*$additiveConst);
            $test_players['chaos_2_win'.$i] = new match_player(4+$j, 3084+$i*$additiveConst);
            $test_players['nc_tonic'.$i] = new match_player(5+$j, 2431+$i*$additiveConst);
            $test_players['havoc'.$i] = new match_player(6+$j, 2102+$i*$additiveConst);
            $test_players['teutonic_tanks'.$i] = new match_player(7+$j, 3078+$i*$additiveConst);
            $test_players['nc_philipphjs'.$i] = new match_player(8+$j, 2917+$i*$additiveConst);
            $test_players['fwb2'.$i] = new match_player(9+$j, 2764+$i*$additiveConst);
            $test_players['ann0r'.$i] = new match_player(10+$j, 2407+$i*$additiveConst);
            $test_players['hooked_on_a_feeling'.$i] = new match_player(11+$j, 679+$i*$additiveConst);
            $test_players['omurchu'.$i] = new match_player(12+$j, 650+$i*$additiveConst);
            $test_players['samedo_sama'.$i] = new match_player(13+$j, 205+$i*$additiveConst);
            $test_players['kalaran'.$i] = new match_player(14+$j, 608+$i*$additiveConst);
            $test_players['trennig'.$i] = new match_player(15+$j, 498+$i*$additiveConst);
            $test_players['lion'.$i] = new match_player(16+$j, 463+$i*$additiveConst);
            $test_players['lion_copy'.$i] = new match_player(17+$j, 463+$i*$additiveConst);
            $test_players['p3375'.$i] = new match_player(18+$j, 3375+$i*$additiveConst);
            $test_players['p2375'.$i] = new match_player(19+$j, 2375+$i*$additiveConst);
            $test_players['p2625'.$i] = new match_player(20+$j, 2625+$i*$additiveConst);
            $test_players['p2425'.$i] = new match_player(21+$j, 2425+$i*$additiveConst);
            $test_players['p1900'.$i] = new match_player(22+$j, 1900+$i*$additiveConst);
            $test_players['p1800'.$i] = new match_player(23+$j, 1800+$i*$additiveConst);
            $test_players['p2025'.$i] = new match_player(24+$j, 2025+$i*$additiveConst);
            $test_players['p1875'.$i] = new match_player(25+$j, 1875+$i*$additiveConst);
            $test_players['p1700'.$i] = new match_player(26+$j, 1700+$i*$additiveConst);
            $test_players['p1801'.$i] = new match_player(27+$j, 1801+$i*$additiveConst);
        }

        $dataSet = [];
        for ($i=0; $i < $iterations; $i++) { 
            $dataSet['2_players'.$i] = [
                $list = match_players_list::from_match_players([
                    $test_players['nili'.$i],
                    $test_players['kalaran'.$i],
                ]),
                $factor = 0.4,
                $expected_teams = [
                    [
                        match_players_list::from_match_players([$test_players['nili'.$i]]),
                        match_players_list::from_match_players([$test_players['kalaran'.$i]]),
                    ],
                ],
            ];
            $dataSet['2_players_reverse'.$i] = [
                $list = match_players_list::from_match_players([
                    $test_players['kalaran'.$i],
                    $test_players['nili'.$i],
                ]),
                $factor = 0.4,
                $expected_teams = [
                    [
                        match_players_list::from_match_players([$test_players['nili'.$i]]),
                        match_players_list::from_match_players([$test_players['kalaran'.$i]]),
                    ],
                ],
            ];
            $dataSet['2_players_same'.$i] = [
                $list = match_players_list::from_match_players([
                    $test_players['lion_copy'.$i],
                    $test_players['lion'.$i],
                ]),
                $factor = 0.4,
                $expected_teams = [
                    [
                        match_players_list::from_match_players([$test_players['lion_copy'.$i]]),
                        match_players_list::from_match_players([$test_players['lion'.$i]]),
                    ],
                ],
            ];
            $dataSet['2_players_same_reverse'.$i] = [
                $list = match_players_list::from_match_players([
                    $test_players['lion'.$i],
                    $test_players['lion_copy'.$i],
                ]),
                $factor = 0.4,
                $expected_teams = [
                    [
                        match_players_list::from_match_players([$test_players['lion'.$i]]),
                        match_players_list::from_match_players([$test_players['lion_copy'.$i]]),
                    ],
                ],
            ];
            $dataSet['8_players'.$i] = [
                $list = match_players_list::from_match_players([
                    $test_players['nc_philipphjs'.$i],
                    $test_players['nc_tonic'.$i],
                    $test_players['ann0r'.$i],
                    $test_players['havoc'.$i],
                    $test_players['chaos_2_win'.$i],
                    $test_players['teutonic_tanks'.$i],
                    $test_players['nili'.$i],
                    $test_players['fwb2'.$i],
                ]),
                $factor = 0.4,
                $expected_teams = [
                    [
                        match_players_list::from_match_players([
                            $test_players['nili'.$i],
                            $test_players['chaos_2_win'.$i],
                            $test_players['nc_tonic'.$i],
                            $test_players['havoc'.$i],
                        ]),
                        match_players_list::from_match_players([
                            $test_players['teutonic_tanks'.$i],
                            $test_players['nc_philipphjs'.$i],
                            $test_players['fwb2'.$i],
                            $test_players['ann0r'.$i],
                        ]),
                    ],
                ],
            ];
            $dataSet['6_players'.$i] = [
                $list = match_players_list::from_match_players([
                    $test_players['lion'.$i],
                    $test_players['trennig'.$i],
                    $test_players['samedo_sama'.$i],
                    $test_players['hooked_on_a_feeling'.$i],
                    $test_players['kalaran'.$i],
                    $test_players['omurchu'.$i],
                ]),
                $factor = 0.4,
                $expected_teams = [
                    [
                        match_players_list::from_match_players([
                            $test_players['hooked_on_a_feeling'.$i],
                            $test_players['omurchu'.$i],
                            $test_players['samedo_sama'.$i],
                        ]),
                        match_players_list::from_match_players([
                            $test_players['kalaran'.$i],
                            $test_players['trennig'.$i],
                            $test_players['lion'.$i],
                        ]),
                    ],
                ],
            ];
            $dataSet['14_players'.$i] = [
                $list = match_players_list::from_match_players([
                    $test_players['ann0r'.$i],
                    $test_players['chaos_2_win'.$i],
                    $test_players['fwb2'.$i],
                    $test_players['havoc'.$i],
                    $test_players['hooked_on_a_feeling'.$i],
                    $test_players['kalaran'.$i],
                    $test_players['lion'.$i],
                    $test_players['nili'.$i],
                    $test_players['nc_philipphjs'.$i],
                    $test_players['nc_tonic'.$i],
                    $test_players['omurchu'.$i],
                    $test_players['samedo_sama'.$i],
                    $test_players['teutonic_tanks'.$i],
                    $test_players['trennig'.$i],
                ]),
                $factor = 0.4,
                $expected_teams = [
                    [
                        match_players_list::from_match_players([
                            $test_players['nili'.$i],
                            $test_players['chaos_2_win'.$i],
                            $test_players['nc_tonic'.$i],
                            $test_players['havoc'.$i],
                        ]),
                        match_players_list::from_match_players([
                            $test_players['teutonic_tanks'.$i],
                            $test_players['nc_philipphjs'.$i],
                            $test_players['fwb2'.$i],
                            $test_players['ann0r'.$i],
                        ]),
                    ],
                    [
                        match_players_list::from_match_players([
                            $test_players['hooked_on_a_feeling'.$i],
                            $test_players['omurchu'.$i],
                            $test_players['samedo_sama'.$i],
                        ]),
                        match_players_list::from_match_players([
                            $test_players['kalaran'.$i],
                            $test_players['trennig'.$i],
                            $test_players['lion'.$i],
                        ]),
                    ],
                ],
            ];
            $dataSet['13_players'.$i] = [
                $list = match_players_list::from_match_players([
                    $test_players['ann0r'.$i],
                    $test_players['chaos_2_win'.$i],
                    $test_players['fwb2'.$i],
                    $test_players['havoc'.$i],
                    $test_players['kalaran'.$i],
                    $test_players['lion'.$i],
                    $test_players['nili'.$i],
                    $test_players['nc_philipphjs'.$i],
                    $test_players['nc_tonic'.$i],
                    $test_players['omurchu'.$i],
                    $test_players['samedo_sama'.$i],
                    $test_players['teutonic_tanks'.$i],
                    $test_players['trennig'.$i], // :( last player in list will be cut off
                ]),
                $factor = 0.4,
                $expected_teams = [
                    [
                        match_players_list::from_match_players([
                            $test_players['nili'.$i],
                            $test_players['teutonic_tanks'.$i],
                            $test_players['nc_tonic'.$i],
                        ]),
                        match_players_list::from_match_players([
                            $test_players['chaos_2_win'.$i],
                            $test_players['nc_philipphjs'.$i],
                            $test_players['fwb2'.$i],
                        ]),
                    ],
                    [
                        match_players_list::from_match_players([
                            $test_players['ann0r'.$i],
                            $test_players['kalaran'.$i],
                            $test_players['samedo_sama'.$i],
                        ]),
                        match_players_list::from_match_players([
                            $test_players['havoc'.$i],
                            $test_players['omurchu'.$i],
                            $test_players['lion'.$i],
                        ]),
                    ],
                ],
            ];
            $dataSet['10_low_factor'.$i] = [
                $list = match_players_list::from_match_players([
                    $test_players['p1700'.$i],
                    $test_players['p2025'.$i],
                    $test_players['p1875'.$i],
                    $test_players['p2425'.$i],
                    $test_players['p1800'.$i],
                    $test_players['p1900'.$i],
                    $test_players['p2375'.$i],
                    $test_players['p3375'.$i],
                    $test_players['p1801'.$i],
                    $test_players['p2625'.$i],
                ]),
                $factor = 0.4,
                $expected_teams = [
                    [
                        match_players_list::from_match_players([
                            $test_players['p3375'.$i],
                            $test_players['p2025'.$i],
                            $test_players['p1900'.$i],
                        ]),
                        match_players_list::from_match_players([
                            $test_players['p2625'.$i],
                            $test_players['p2425'.$i],
                            $test_players['p2375'.$i],
                        ]),
                    ],
                    [
                        match_players_list::from_match_players([
                            $test_players['p1875'.$i],
                            $test_players['p1700'.$i],
                        ]),
                        match_players_list::from_match_players([
                            $test_players['p1801'.$i],
                            $test_players['p1800'.$i],
                        ]),
                    ],
                ],
            ];

            $dataSet['10_very_low_factor'.$i] = [
                $list = match_players_list::from_match_players([
                    $test_players['p1700'.$i],
                    $test_players['p2025'.$i],
                    $test_players['p1875'.$i],
                    $test_players['p2425'.$i],
                    $test_players['p1800'.$i],
                    $test_players['p1900'.$i],
                    $test_players['p2375'.$i],
                    $test_players['p3375'.$i],
                    $test_players['p1801'.$i],
                    $test_players['p2625'.$i],
                ]),
                $factor = -10, //should be automatically increased to 0.0
                $expected_teams = [
                    [
                        match_players_list::from_match_players([
                            $test_players['p3375'.$i],
                            $test_players['p2025'.$i],
                            $test_players['p1900'.$i],
                        ]),
                        match_players_list::from_match_players([
                            $test_players['p2625'.$i],
                            $test_players['p2425'.$i],
                            $test_players['p2375'.$i],
                        ]),
                    ],
                    [
                        match_players_list::from_match_players([
                            $test_players['p1875'.$i],
                            $test_players['p1700'.$i],
                        ]),
                        match_players_list::from_match_players([
                            $test_players['p1801'.$i],
                            $test_players['p1800'.$i],
                        ]),
                    ],
                ],
            ];
            $dataSet['10_high_factor'.$i] = [
                $list = match_players_list::from_match_players([
                    $test_players['p1700'.$i],
                    $test_players['p2025'.$i],
                    $test_players['p1875'.$i],
                    $test_players['p2425'.$i],
                    $test_players['p1800'.$i],
                    $test_players['p1900'.$i],
                    $test_players['p2375'.$i],
                    $test_players['p3375'.$i],
                    $test_players['p1801'.$i],
                    $test_players['p2625'.$i],
                ]),
                $factor = 0.6,
                $expected_teams = [
                    [
                        match_players_list::from_match_players([
                            $test_players['p3375'.$i],
                            $test_players['p2375'.$i],
                        ]),
                        match_players_list::from_match_players([
                            $test_players['p2625'.$i],
                            $test_players['p2425'.$i],
                        ]),
                    ],
                    [
                        match_players_list::from_match_players([
                            $test_players['p2025'.$i],
                            $test_players['p1801'.$i],
                            $test_players['p1700'.$i],
                        ]),
                        match_players_list::from_match_players([
                            $test_players['p1900'.$i],
                            $test_players['p1875'.$i],
                            $test_players['p1800'.$i],
                        ]),
                    ],
                ],
            ];
            $dataSet['10_very_high_factor'.$i] = [
                $list = match_players_list::from_match_players([
                    $test_players['p1700'.$i],
                    $test_players['p2025'.$i],
                    $test_players['p1875'.$i],
                    $test_players['p2425'.$i],
                    $test_players['p1800'.$i],
                    $test_players['p1900'.$i],
                    $test_players['p2375'.$i],
                    $test_players['p3375'.$i],
                    $test_players['p1801'.$i],
                    $test_players['p2625'.$i],
                ]),
                $factor = 10, //should be automatically be reduced to 0.0
                $expected_teams = [
                    [
                        match_players_list::from_match_players([
                            $test_players['p3375'.$i],
                            $test_players['p2025'.$i],
                            $test_players['p1900'.$i],
                        ]),
                        match_players_list::from_match_players([
                            $test_players['p2625'.$i],
                            $test_players['p2425'.$i],
                            $test_players['p2375'.$i],
                        ]),
                    ],
                    [
                        match_players_list::from_match_players([
                            $test_players['p1875'.$i],
                            $test_players['p1700'.$i],
                        ]),
                        match_players_list::from_match_players([
                            $test_players['p1801'.$i],
                            $test_players['p1800'.$i],
                        ]),
                    ],
                ],
            ];
        }
        return $dataSet;
    }
}