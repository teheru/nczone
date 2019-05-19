<?php

namespace eru\nczone\tests\zone;

use eru\nczone\zone\entity\match_player;
use eru\nczone\zone\entity\match_players_list;

class match_players_list_test extends \phpbb_test_case
{
    /**
     * @dataProvider from_match_players_data_provider
     */
    public function test_from_match_players(array $players)
    {
        $list = match_players_list::from_match_players($players);
        $count = \count($players);
        self::assertSame($count, $list->length());
        for ($i = 0; $i < $count; $i++) {
            self::assertSame($players[$i], $list->shift());
        }
        self::assertSame(0, $list->length());
    }

    public function from_match_players_data_provider()
    {
        return [
            [
                $players = [
                    new match_player(1, 1337),
                    new match_player(2, 501),
                    new match_player(3, 102),
                    new match_player(4, 4000),
                ],
            ]
        ];
    }

    /**
     * @dataProvider get_max_rating_data_provider
     */
    public function test_get_max_rating(array $players, int $expected): void
    {
        $list = match_players_list::from_match_players($players);
        $this->assertSame($expected, $list->get_max_rating());
    }

    /**
     * @dataProvider get_min_rating_data_provider
     */
    public function test_get_min_rating(array $players, int $expected): void
    {
        $list = match_players_list::from_match_players($players);
        $this->assertSame($expected, $list->get_min_rating());
    }

    /**
     * @dataProvider get_abs_rating_variance_data_provider
     */
    public function test_get_abs_rating_variance(array $players, int $expected): void
    {
        $list = match_players_list::from_match_players($players);
        $this->assertSame($expected, match_players_list::get_abs_rating_variance($list));
    }

    public function get_max_rating_data_provider(): array
    {
        return [
            [
                $player_lists = [
                    new match_player(6, 59),
                    new match_player(7, 45),
                    new match_player(8, 8000),
                    new match_player(9, 501),
                    new match_player(10, 2300),
                ],
                $expected = 8000,
            ],
            [
                $player_lists = [
                ],
                $expected = 0,
            ],
        ];
    }

    public function get_min_rating_data_provider(): array
    {
        return [
            [
                $player_lists = [
                    new match_player(6, 59),
                    new match_player(7, 45),
                    new match_player(8, 8000),
                    new match_player(9, 501),
                    new match_player(10, 2300),
                ],
                $expected = 45,
            ],
            [
                $player_lists = [
                    new match_player(1, 999),
                    new match_player(2, 1000),
                    new match_player(3, 8000),
                    new match_player(4, 501),
                    new match_player(5, 2300),
                ],
                $expected = 501,
            ],
            [
                $player_lists = [
                ],
                $expected = 0,
            ],
        ];
    }

    public function get_abs_rating_variance_data_provider(): array
    {
        $const = 2000;
        return [
            [
                $player_lists = [
                    new match_player(6, 59),
                    new match_player(7, 45),
                    new match_player(8, 8000),
                    new match_player(9, 501),
                    new match_player(10, 2300),
                ],
                $expected = 11876,
            ],
            [
                $player_lists = [
                    new match_player(6, 59-$const),
                    new match_player(7, 45-$const),
                    new match_player(8, 8000-$const),
                    new match_player(9, 501-$const),
                    new match_player(10, 2300-$const),
                ],
                $expected = 11876,
            ],
            [
                $player_lists = [
                ],
                $expected = 0,
            ],
        ];
    }

}
