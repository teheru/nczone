<?php

namespace eru\nczone\tests\zone;

use eru\nczone\zone\players;
use PHPUnit\Framework\TestCase;

class players_test extends TestCase
{
    /**
     * @dataProvider calculate_new_streak_data_provider
     */
    public function test_calculate_new_streak(bool $winner, int $last_streak, int $expected)
    {
        $this->assertSame($expected, players::calculate_new_streak($winner, $last_streak));
    }

    public function calculate_new_streak_data_provider()
    {
        return [
            [true, 0, 1],
            [true, -1, 1],
            [true, -10, 1],
            [true, 1, 2],
            [true, 10, 11],
            [false, 0, -1],
            [false, -1, -2],
            [false, -10, -11],
            [false, 1, -1],
            [false, 10, -1],
        ];
    }
}
