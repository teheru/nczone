<?php

namespace eru\nczone\tests\zone\entity;

use eru\nczone\zone\entity\draw_match;
use eru\nczone\zone\entity\match_player;
use eru\nczone\zone\entity\match_players_list;

class draw_match_test extends \phpbb_test_case
{
    /**
     * @dataProvider get_all_player_ids_data_provider
     * @param draw_match $draw_match
     * @param array $expected_ids
     * @throws \Exception
     */
    public function test_get_all_player_ids(
        draw_match $draw_match,
        array $expected_ids
    ) {
        self::assertEquals($expected_ids, $draw_match->get_all_player_ids());
    }

    public function get_all_player_ids_data_provider(): array
    {
        $ids = [1, 6, 8, 19];
        $ids2 = [50, 56, 68, 3];

        return [
            [
                new draw_match(
                    match_players_list::from_match_players(\array_map(function ($id) {
                        return new match_player($id, $id * 100);
                    }, $ids)),
                    match_players_list::from_match_players(\array_map(function ($id) {
                        return new match_player($id, $id * 100);
                    }, $ids2))
                ),
                \array_merge($ids, $ids2),
            ],
        ];
    }
}
