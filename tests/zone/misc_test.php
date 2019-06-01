<?php

namespace eru\nczone\tests\zone;

use eru\nczone\zone\misc;

class misc_test extends \phpbb_test_case
{
    /**
     * @dataProvider cut_to_same_length_data_provider
     * @param array $arrays
     * @param $expected
     */
    public function test_cut_to_same_length(array $arrays, $expected): void
    {
        self::assertSame($expected, misc::cut_to_same_length(...$arrays));
    }

    public function cut_to_same_length_data_provider()
    {
        return [
            'same_length' => [
                [
                    ['hallo', 'was', 'geht', 'ab'],
                    ['hallo', 'was', 'geht', 'down'],
                ],
                [
                    ['hallo', 'was', 'geht', 'ab'],
                    ['hallo', 'was', 'geht', 'down'],
                ],
            ],
            'first_smaller' => [
                [
                    ['hallo', 'was'],
                    ['hallo', 'was', 'geht', 'down'],
                ],
                [
                    ['hallo', 'was'],
                    ['hallo', 'was'],
                ],
            ],
            'last_smaller' => [
                [
                    ['hallo', 'was', 'geht', 'down'],
                    ['hallo', 'was'],
                ],
                [
                    ['hallo', 'was'],
                    ['hallo', 'was'],
                ],
            ],
            'empty' => [
                [
                    ['hallo', 'was', 'geht', 'down'],
                    [],
                ],
                [
                    [],
                    [],
                ],
            ],
            'more_than_two' => [
                [
                    ['hallo', 'was', 'geht', 'down'],
                    ['hallo', 'was', 'down'],
                    ['hallo', 'was', 'geht', 'down'],
                    ['hallo', ['some' => 'stuff']],
                    [56, 1, 0, 1, 6, 5],
                ],
                [
                    ['hallo', 'was'],
                    ['hallo', 'was'],
                    ['hallo', 'was'],
                    ['hallo', ['some' => 'stuff']],
                    [56, 1],
                ]
            ],
        ];
    }
}
