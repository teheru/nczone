<?php

namespace eru\nczone\tests\zone;

use eru\nczone\zone\draw_teams;
use eru\nczone\zone\entity\draw_match;
use eru\nczone\zone\entity\match_player;
use eru\nczone\zone\entity\match_players_list;

class draw_teams_test extends \phpbb_test_case
{
    /**
     * @dataProvider make_matches_data_provider
     * @throws \Exception
     */
    public function test_make_matches($list, $factor, $expected_matches)
    {
        $dt = new draw_teams();
        self::assertEquals($expected_matches, $dt->make_matches($list, $factor, 0, 0));
    }

    public function make_matches_data_provider()
    {
        $additive_const = 1000; //a constant that is added from each rating for each test set
        $iterations = 2;
        $test_players = array();
        $j = 0;
        for ($i=0; $i < $iterations; $i++) {
            $test_players['nili'.$i] = new match_player(3 + $j, 3417 + $i * $additive_const);
            $test_players['chaos_2_win'.$i] = new match_player(4 + $j, 3084 + $i * $additive_const);
            $test_players['nc_tonic'.$i] = new match_player(5 + $j, 2431 + $i * $additive_const);
            $test_players['havoc'.$i] = new match_player(6 + $j, 2102 + $i * $additive_const);
            $test_players['teutonic_tanks'.$i] = new match_player(7 + $j, 3078 + $i * $additive_const);
            $test_players['nc_philipphjs'.$i] = new match_player(8 + $j, 2917 + $i * $additive_const);
            $test_players['fwb2'.$i] = new match_player(9 + $j, 2764 + $i * $additive_const);
            $test_players['ann0r'.$i] = new match_player(10 + $j, 2407 + $i * $additive_const);
            $test_players['hooked_on_a_feeling'.$i] = new match_player(11 + $j, 679 + $i * $additive_const);
            $test_players['omurchu'.$i] = new match_player(12 + $j, 650 + $i * $additive_const);
            $test_players['samedo_sama'.$i] = new match_player(13 + $j, 205 + $i * $additive_const);
            $test_players['kalaran'.$i] = new match_player(14 + $j, 608 + $i * $additive_const);
            $test_players['trennig'.$i] = new match_player(15 + $j, 498 + $i * $additive_const);
            $test_players['lion'.$i] = new match_player(16 + $j, 463 + $i * $additive_const);
            $test_players['lion_copy'.$i] = new match_player(17 + $j, 463 + $i * $additive_const);
            $test_players['p3375'.$i] = new match_player(18 + $j, 3375 + $i * $additive_const);
            $test_players['p2375'.$i] = new match_player(19 + $j, 2375 + $i * $additive_const);
            $test_players['p2625'.$i] = new match_player(20 + $j, 2625 + $i * $additive_const);
            $test_players['p2425'.$i] = new match_player(21 + $j, 2425 + $i * $additive_const);
            $test_players['p1900'.$i] = new match_player(22 + $j, 1900 + $i * $additive_const);
            $test_players['p1800'.$i] = new match_player(23 + $j, 1800 + $i * $additive_const);
            $test_players['p2025'.$i] = new match_player(24 + $j, 2025 + $i * $additive_const);
            $test_players['p1875'.$i] = new match_player(25 + $j, 1875 + $i * $additive_const);
            $test_players['p1700'.$i] = new match_player(26 + $j, 1700 + $i * $additive_const);
            $test_players['p1801'.$i] = new match_player(27 + $j, 1801 + $i * $additive_const);
        }

        $data_set = [];
        for ($i=0; $i < $iterations; $i++) { 
            $data_set['2_players'.$i] = [
                $list = match_players_list::from_match_players([
                    $test_players['nili'.$i],
                    $test_players['kalaran'.$i],
                ]),
                $factor = 0.4,
                $expected_matches = [
                    new draw_match(
                        match_players_list::from_match_players([$test_players['nili'.$i]]),
                        match_players_list::from_match_players([$test_players['kalaran'.$i]])
                    ),
                ],
            ];
            $data_set['2_players_reverse'.$i] = [
                $list = match_players_list::from_match_players([
                    $test_players['kalaran'.$i],
                    $test_players['nili'.$i],
                ]),
                $factor = 0.4,
                $expected_matches = [
                    new draw_match(
                        match_players_list::from_match_players([$test_players['nili'.$i]]),
                        match_players_list::from_match_players([$test_players['kalaran'.$i]])
                    ),
                ],
            ];
            $data_set['2_players_same'.$i] = [
                $list = match_players_list::from_match_players([
                    $test_players['lion_copy'.$i],
                    $test_players['lion'.$i],
                ]),
                $factor = 0.4,
                $expected_matches = [
                    new draw_match(
                        match_players_list::from_match_players([$test_players['lion_copy'.$i]]),
                        match_players_list::from_match_players([$test_players['lion'.$i]])
                    ),
                ],
            ];
            $data_set['2_players_same_reverse'.$i] = [
                $list = match_players_list::from_match_players([
                    $test_players['lion'.$i],
                    $test_players['lion_copy'.$i],
                ]),
                $factor = 0.4,
                $expected_matches = [
                    new draw_match(
                        match_players_list::from_match_players([$test_players['lion'.$i]]),
                        match_players_list::from_match_players([$test_players['lion_copy'.$i]])
                    ),
                ],
            ];

            $data_set['8_players'.$i] = [
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
                $expected_matches = [
                    new draw_match(
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
                        ])
                    ),
                ],
            ];

            $data_set['6_players'.$i] = [
                $list = match_players_list::from_match_players([
                    $test_players['lion'.$i],
                    $test_players['trennig'.$i],
                    $test_players['samedo_sama'.$i],
                    $test_players['hooked_on_a_feeling'.$i],
                    $test_players['kalaran'.$i],
                    $test_players['omurchu'.$i],
                ]),
                $factor = 0.4,
                $expected_matches = [
                    new draw_match(
                        match_players_list::from_match_players([
                            $test_players['hooked_on_a_feeling'.$i],
                            $test_players['omurchu'.$i],
                            $test_players['samedo_sama'.$i],
                        ]),
                        match_players_list::from_match_players([
                            $test_players['kalaran'.$i],
                            $test_players['trennig'.$i],
                            $test_players['lion'.$i],
                        ])
                    ),
                ],
            ];
            $data_set['14_players'.$i] = [
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
                $expected_matches = [
                    new draw_match(
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
                        ])
                    ),
                    new draw_match(
                        match_players_list::from_match_players([
                            $test_players['hooked_on_a_feeling'.$i],
                            $test_players['omurchu'.$i],
                            $test_players['samedo_sama'.$i],
                        ]),
                        match_players_list::from_match_players([
                            $test_players['kalaran'.$i],
                            $test_players['trennig'.$i],
                            $test_players['lion'.$i],
                        ])
                    ),
                ],
            ];
            $data_set['13_players'.$i] = [
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
                $expected_matches = [
                    new draw_match(
                        match_players_list::from_match_players([
                            $test_players['nili'.$i],
                            $test_players['teutonic_tanks'.$i],
                            $test_players['nc_tonic'.$i],
                        ]),
                        match_players_list::from_match_players([
                            $test_players['chaos_2_win'.$i],
                            $test_players['nc_philipphjs'.$i],
                            $test_players['fwb2'.$i],
                        ])
                    ),
                    new draw_match(
                        match_players_list::from_match_players([
                            $test_players['ann0r'.$i],
                            $test_players['kalaran'.$i],
                            $test_players['samedo_sama'.$i],
                        ]),
                        match_players_list::from_match_players([
                            $test_players['havoc'.$i],
                            $test_players['omurchu'.$i],
                            $test_players['lion'.$i],
                        ])
                    ),
                ],
            ];
            $data_set['10_low_factor'.$i] = [
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
                $expected_matches = [
                    new draw_match(
                        match_players_list::from_match_players([
                            $test_players['p3375'.$i],
                            $test_players['p2025'.$i],
                            $test_players['p1900'.$i],
                        ]),
                        match_players_list::from_match_players([
                            $test_players['p2625'.$i],
                            $test_players['p2425'.$i],
                            $test_players['p2375'.$i],
                        ])
                    ),
                    new draw_match(
                        match_players_list::from_match_players([
                            $test_players['p1875'.$i],
                            $test_players['p1700'.$i],
                        ]),
                        match_players_list::from_match_players([
                            $test_players['p1801'.$i],
                            $test_players['p1800'.$i],
                        ])
                    ),
                ],
            ];
            $data_set['10_very_low_factor'.$i] = [
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
                $expected_matches = [
                    new draw_match(
                        match_players_list::from_match_players([
                            $test_players['p3375'.$i],
                            $test_players['p2025'.$i],
                            $test_players['p1900'.$i],
                        ]),
                        match_players_list::from_match_players([
                            $test_players['p2625'.$i],
                            $test_players['p2425'.$i],
                            $test_players['p2375'.$i],
                        ])
                    ),
                    new draw_match(
                        match_players_list::from_match_players([
                            $test_players['p1875'.$i],
                            $test_players['p1700'.$i],
                        ]),
                        match_players_list::from_match_players([
                            $test_players['p1801'.$i],
                            $test_players['p1800'.$i],
                        ])
                    ),
                ],
            ];
            $data_set['10_high_factor'.$i] = [
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
                $expected_matches = [
                    new draw_match(
                        match_players_list::from_match_players([
                            $test_players['p3375'.$i],
                            $test_players['p2375'.$i],
                        ]),
                        match_players_list::from_match_players([
                            $test_players['p2625'.$i],
                            $test_players['p2425'.$i],
                        ])
                    ),
                    new draw_match(
                        match_players_list::from_match_players([
                            $test_players['p2025'.$i],
                            $test_players['p1801'.$i],
                            $test_players['p1700'.$i],
                        ]),
                        match_players_list::from_match_players([
                            $test_players['p1900'.$i],
                            $test_players['p1875'.$i],
                            $test_players['p1800'.$i],
                        ])
                    ),
                ],
            ];
            $data_set['10_very_high_factor'.$i] = [
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
                $expected_matches = [
                    new draw_match(
                        match_players_list::from_match_players([
                            $test_players['p3375'.$i],
                            $test_players['p2025'.$i],
                            $test_players['p1900'.$i],
                        ]),
                        match_players_list::from_match_players([
                            $test_players['p2625'.$i],
                            $test_players['p2425'.$i],
                            $test_players['p2375'.$i],
                        ])
                    ),
                    new draw_match(
                        match_players_list::from_match_players([
                            $test_players['p1875'.$i],
                            $test_players['p1700'.$i],
                        ]),
                        match_players_list::from_match_players([
                            $test_players['p1801'.$i],
                            $test_players['p1800'.$i],
                        ])
                    ),
                ],
            ];
        }
        return $data_set;
    }

    /**
     * @dataProvider switch_players_indices_data_provider
     * @throws \Exception
     */
    public function test_switch_players_indices(int $number_matches, array $expected_values) {
        $dt = new draw_teams();
        self::assertEquals($expected_values, $dt->switch_players_indices($number_matches));
    }

    public function switch_players_indices_data_provider() {
        return [
            'single_match' => [
                1, [[0]]
            ],

            'two_matches' => [
                2, [[1, 1]]
            ],

            'three_matches' => [
                3, [[1, 1, 0], [0, 1, 1]]
            ],

            'four_matches' => [
                4, [[1, 1, 2, 2], [0, 1, 1, 0]]
            ],

            'five_matches' => [
                5, [[1, 1, 2, 2, 0], [0, 1, 1, 2, 2]]
            ]
        ];
    }


    /**
     * @dataProvider permute_edge_players_data_provider
     * @throws \Exception
     */
    public function test_permute_edge_players(
        $upper_players,
        $lower_players,
        int $number,
        array $expected_values
    ) {
        $dt = new draw_teams();
        self::assertEquals($expected_values, $dt->permute_edge_players(
            $upper_players,
            $lower_players,
            $number
        ));
    }

    public function permute_edge_players_data_provider() {
        $test_players = [];

        $test_players['p11'] = new match_player(100, 1700);
        $test_players['p12'] = new match_player(101, 1600);
        $test_players['p13'] = new match_player(102, 1500);
        $test_players['p14'] = new match_player(103, 1400);
        $test_players['p15'] = new match_player(104, 1300);
        $test_players['p16'] = new match_player(105, 1200);
        $test_players['p17'] = new match_player(106, 1100);
        $test_players['p18'] = new match_player(107, 1000);

        $test_players['p21'] = new match_player(200, 2700);
        $test_players['p22'] = new match_player(201, 2600);
        $test_players['p23'] = new match_player(202, 2500);
        $test_players['p24'] = new match_player(203, 2400);
        $test_players['p25'] = new match_player(204, 2300);
        $test_players['p26'] = new match_player(205, 2200);
        $test_players['p27'] = new match_player(206, 2100);
        $test_players['p28'] = new match_player(207, 2000);

        $match_2vs2 = [
            match_players_list::from_match_players([
                $test_players['p21'],
                $test_players['p22'],
                $test_players['p23'],
                $test_players['p24']
            ]),
            match_players_list::from_match_players([
                $test_players['p11'],
                $test_players['p12'],
                $test_players['p13'],
                $test_players['p14']
            ])
        ];

        $match_3vs3 = [
            match_players_list::from_match_players([
                $test_players['p21'],
                $test_players['p22'],
                $test_players['p23'],
                $test_players['p24'],
                $test_players['p25'],
                $test_players['p26']
            ]),
            match_players_list::from_match_players([
                $test_players['p11'],
                $test_players['p12'],
                $test_players['p13'],
                $test_players['p14'],
                $test_players['p15'],
                $test_players['p16']
            ])
        ];

        $match_4vs4 = [
            match_players_list::from_match_players([
                $test_players['p21'],
                $test_players['p22'],
                $test_players['p23'],
                $test_players['p24'],
                $test_players['p25'],
                $test_players['p26'],
                $test_players['p27'],
                $test_players['p28']
            ]),
            match_players_list::from_match_players([
                $test_players['p11'],
                $test_players['p12'],
                $test_players['p13'],
                $test_players['p14'],
                $test_players['p15'],
                $test_players['p16'],
                $test_players['p17'],
                $test_players['p18']
            ])
        ];

        return [
            '2vs2_w_0' => [
                $match_2vs2[0],
                $match_2vs2[1],
                0,
                [$match_2vs2]
            ],

            '3vs3_w_0' => [
                $match_3vs3[0],
                $match_3vs3[1],
                0,
                [$match_3vs3]
            ],

            '4vs4_w_0' => [
                $match_4vs4[0],
                $match_4vs4[1],
                0,
                [$match_4vs4]
            ],

            '2vs2_w_1' => [
                $match_2vs2[0],
                $match_2vs2[1],
                1,
                [
                    $match_2vs2,
                    [
                        match_players_list::from_match_players([
                            $test_players['p21'],
                            $test_players['p22'],
                            $test_players['p23'],
                            $test_players['p11']
                        ]),
                        match_players_list::from_match_players([
                            $test_players['p24'],
                            $test_players['p12'],
                            $test_players['p13'],
                            $test_players['p14']
                        ])
                    ]
                ]
            ],

            '3vs3_w_1' => [
                $match_3vs3[0],
                $match_3vs3[1],
                1,
                [
                    $match_3vs3,
                    [
                        match_players_list::from_match_players([
                            $test_players['p21'],
                            $test_players['p22'],
                            $test_players['p23'],
                            $test_players['p24'],
                            $test_players['p25'],
                            $test_players['p11']
                        ]),
                        match_players_list::from_match_players([
                            $test_players['p26'],
                            $test_players['p12'],
                            $test_players['p13'],
                            $test_players['p14'],
                            $test_players['p15'],
                            $test_players['p16']
                        ])
                    ]
                ]
            ],

            '4vs4_w_1' => [
                $match_4vs4[0],
                $match_4vs4[1],
                1,
                [
                    $match_4vs4,
                    [
                        match_players_list::from_match_players([
                            $test_players['p21'],
                            $test_players['p22'],
                            $test_players['p23'],
                            $test_players['p24'],
                            $test_players['p25'],
                            $test_players['p26'],
                            $test_players['p27'],
                            $test_players['p11']
                        ]),
                        match_players_list::from_match_players([
                            $test_players['p28'],
                            $test_players['p12'],
                            $test_players['p13'],
                            $test_players['p14'],
                            $test_players['p15'],
                            $test_players['p16'],
                            $test_players['p17'],
                            $test_players['p18']
                        ])
                    ]
                ]
            ],

            '2vs2_w_2' => [
                $match_2vs2[0],
                $match_2vs2[1],
                2,
                [
                    0 => $match_2vs2,
                    1 => [
                        match_players_list::from_match_players([
                            $test_players['p21'],
                            $test_players['p22'],
                            $test_players['p23'],
                            $test_players['p11']
                        ]),
                        match_players_list::from_match_players([
                            $test_players['p24'],
                            $test_players['p12'],
                            $test_players['p13'],
                            $test_players['p14']
                        ])
                    ],
                    2 => [
                        match_players_list::from_match_players([
                            $test_players['p21'],
                            $test_players['p22'],
                            $test_players['p23'],
                            $test_players['p12']
                        ]),
                        match_players_list::from_match_players([
                            $test_players['p24'],
                            $test_players['p11'],
                            $test_players['p13'],
                            $test_players['p14']
                        ])
                    ],
                    3 => [
                        match_players_list::from_match_players([
                            $test_players['p21'],
                            $test_players['p22'],
                            $test_players['p24'],
                            $test_players['p11']
                        ]),
                        match_players_list::from_match_players([
                            $test_players['p23'],
                            $test_players['p12'],
                            $test_players['p13'],
                            $test_players['p14']
                        ])
                    ],
                    4 => [
                        match_players_list::from_match_players([
                            $test_players['p21'],
                            $test_players['p22'],
                            $test_players['p24'],
                            $test_players['p12']
                        ]),
                        match_players_list::from_match_players([
                            $test_players['p23'],
                            $test_players['p11'],
                            $test_players['p13'],
                            $test_players['p14']
                        ])
                    ],
                    5 => [
                        match_players_list::from_match_players([
                            $test_players['p21'],
                            $test_players['p22'],
                            $test_players['p11'],
                            $test_players['p12']
                        ]),
                        match_players_list::from_match_players([
                            $test_players['p23'],
                            $test_players['p24'],
                            $test_players['p13'],
                            $test_players['p14']
                        ])
                    ]
                ]
            ],

            '3vs3_w_2' => [
                $match_3vs3[0],
                $match_3vs3[1],
                2,
                [
                    0 => $match_3vs3,
                    1 => [
                        match_players_list::from_match_players([
                            $test_players['p21'],
                            $test_players['p22'],
                            $test_players['p23'],
                            $test_players['p24'],
                            $test_players['p25'],
                            $test_players['p11']
                        ]),
                        match_players_list::from_match_players([
                            $test_players['p26'],
                            $test_players['p12'],
                            $test_players['p13'],
                            $test_players['p14'],
                            $test_players['p15'],
                            $test_players['p16']
                        ])
                    ],
                    2 => [
                        match_players_list::from_match_players([
                            $test_players['p21'],
                            $test_players['p22'],
                            $test_players['p23'],
                            $test_players['p24'],
                            $test_players['p25'],
                            $test_players['p12']
                        ]),
                        match_players_list::from_match_players([
                            $test_players['p26'],
                            $test_players['p11'],
                            $test_players['p13'],
                            $test_players['p14'],
                            $test_players['p15'],
                            $test_players['p16']
                        ])
                    ],
                    3 => [
                        match_players_list::from_match_players([
                            $test_players['p21'],
                            $test_players['p22'],
                            $test_players['p23'],
                            $test_players['p24'],
                            $test_players['p26'],
                            $test_players['p11']
                        ]),
                        match_players_list::from_match_players([
                            $test_players['p25'],
                            $test_players['p12'],
                            $test_players['p13'],
                            $test_players['p14'],
                            $test_players['p15'],
                            $test_players['p16']
                        ])
                    ],
                    4 => [
                        match_players_list::from_match_players([
                            $test_players['p21'],
                            $test_players['p22'],
                            $test_players['p23'],
                            $test_players['p24'],
                            $test_players['p26'],
                            $test_players['p12']
                        ]),
                        match_players_list::from_match_players([
                            $test_players['p25'],
                            $test_players['p11'],
                            $test_players['p13'],
                            $test_players['p14'],
                            $test_players['p15'],
                            $test_players['p16']
                        ])
                    ],
                    5 => [
                        match_players_list::from_match_players([
                            $test_players['p21'],
                            $test_players['p22'],
                            $test_players['p23'],
                            $test_players['p24'],
                            $test_players['p11'],
                            $test_players['p12']
                        ]),
                        match_players_list::from_match_players([
                            $test_players['p25'],
                            $test_players['p26'],
                            $test_players['p13'],
                            $test_players['p14'],
                            $test_players['p15'],
                            $test_players['p16']
                        ])
                    ]
                ]
            ],
        ];
    }
}
