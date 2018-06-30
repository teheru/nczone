<?php

namespace eru\nczone\config;

class config
{
    private static $map = [
        'nczone_activity_cron' => 0,

        'nczone_rules_post_id' => 0,
        'nczone_match_forum_id' => 0,
        'nczone_pmatches_page_size' => 10,
        'nczone_activity_time' => 56,
        'nczone_activity_1' => 14,
        'nczone_activity_2' => 28,
        'nczone_activity_3' => 56,
        'nczone_activity_4' => 112,
        'nczone_activity_5' => 224,
        'nczone_draw_time' => 60,
        'nczone_bet_time' => 1200,
        'nczone_info_posts' => '',
        'nczone_draw_player_civs' => 600,
        'nczone_draw_team_civs' => 120,
        'nczone_draw_match_extra_civs_1vs1' => 7,
        'nczone_draw_match_extra_civs_2vs2' => 6,
        'nczone_draw_match_extra_civs_3vs3' => 5,
        'nczone_draw_match_extra_civs_4vs4' => 4,
        'nczone_draw_team_extra_civs_1vs1' => 5,
        'nczone_draw_team_extra_civs_2vs2' => 4,
        'nczone_draw_team_extra_civs_3vs3' => 3,
        'nczone_draw_team_extra_civs_4vs4' => 2,
        'nczone_draw_player_num_civs_1vs1' => 9,
        'nczone_draw_player_num_civs_2vs2' => 5,
        'nczone_draw_player_num_civs_3vs3' => 4,
        'nczone_draw_player_num_civs_4vs4' => 3,
    ];

    public static function get(string $key, $default = null)
    {
        return self::_config()[$key] ?? self::default($key, $default);
    }

    public static function set(string $key, $value, $use_cache = true)
    {
        return self::_config()->set($key, $value, $use_cache);
    }

    public static function default(string $key, $default = null)
    {
        return self::$map[$key] ?? $default;
    }

    # todo: try to solve this with dependency injection instead
    private static function _config(): \phpbb\config\config
    {
        return $GLOBALS['config'];
    }
}
