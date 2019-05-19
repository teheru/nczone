<?php

namespace eru\nczone\tests\utility;

use eru\nczone\utility\number_util;

class number_util_test extends \phpbb_test_case
{
    /**
     * @dataProvider cmp_data_provider
     */
    public function test_cmp($num1, $num2, int $expected): void
    {
        $this->assertSame($expected, number_util::cmp($num1, $num2));
    }

    public function cmp_data_provider(): array
    {
        return [
            [4, 5, $expected = 1],
            [10, -4, $expected = -1],
            [10, 10, $expected = 0],
            [10, 19, $expected = 1],
            [null, 0, $expected = 0],
            ["bla", 0, $expected = 0],
            ["A", "778", $expected = -1],
        ];
    }
}
