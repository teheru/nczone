<?php

namespace eru\nczone\tests\zone;

use eru\nczone\zone\players;
use PHPUnit\Framework\TestCase;

class players_test extends TestCase
{
    /**
     * @dataProvider get_max_rating_data_provider
     */
    public function test_get_max_rating(array $player_lists, int $expected): void
    {
        $this->assertSame($expected, players::get_max_rating(...$player_lists));
    }

    /**
     * @dataProvider get_min_rating_data_provider
     */
    public function test_get_min_rating(array $player_lists, int $expected): void
    {
        $this->assertSame($expected, players::get_min_rating(...$player_lists));
    }

    public function get_max_rating_data_provider(): array
    {
        return [
            [
                $player_lists = [
                    [
                        ['rating' => 699],
                        ['rating' => 1888],
                        ['rating' => 2000],
                        ['rating' => 500],
                        ['rating' => 1400],
                    ],
                    [
                        ['rating' => 59],
                        ['rating' => 45],
                        ['rating' => 8000],
                        ['rating' => 501],
                        ['rating' => 2300],
                    ],
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
                    [
                        ['rating' => 699],
                        ['rating' => 1888],
                        ['rating' => 2000],
                        ['rating' => 500],
                        ['rating' => 1400],
                    ],
                    [
                        ['rating' => 59],
                        ['rating' => 45],
                        ['rating' => 8000],
                        ['rating' => 501],
                        ['rating' => 2300],
                    ],
                ],
                $expected = 45,
            ],
            [
                $player_lists = [
                    [
                        ['rating' => 999],
                        ['rating' => 1000],
                        ['rating' => 8000],
                        ['rating' => 501],
                        ['rating' => 2300],
                    ],
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
}
