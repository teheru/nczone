<?php

namespace eru\nczone\config;

final class config
{
    public const activity_cron = 'nczone_activity_cron';

    public const CRON_CONFIG_KEYS = [
        self::activity_cron,
    ];

    public const rules_post_id = 'nczone_rules_post_id';
    public const match_forum_id = 'nczone_match_forum_id';
    public const pmatches_page_size = 'nczone_pmatches_page_size';
    public const activity_time = 'nczone_activity_time';
    public const activity_1 = 'nczone_activity_1';
    public const activity_2 = 'nczone_activity_2';
    public const activity_3 = 'nczone_activity_3';
    public const activity_4 = 'nczone_activity_4';
    public const activity_5 = 'nczone_activity_5';
    public const draw_time = 'nczone_draw_time';
    public const bet_time = 'nczone_bet_time';
    public const points_1vs1 = 'nczone_points_1vs1';
    public const points_2vs2 = 'nczone_points_2vs2';
    public const points_3vs3 = 'nczone_points_3vs3';
    public const points_4vs4 = 'nczone_points_4vs4';
    public const extra_points = 'nczone_extra_points';
    public const info_posts = 'nczone_info_posts';
    public const draw_player_civs = 'nczone_draw_player_civs';
    public const draw_team_civs = 'nczone_draw_team_civs';
    public const draw_match_extra_civs_1vs1 = 'nczone_draw_match_extra_civs_1vs1';
    public const draw_match_extra_civs_2vs2 = 'nczone_draw_match_extra_civs_2vs2';
    public const draw_match_extra_civs_3vs3 = 'nczone_draw_match_extra_civs_3vs3';
    public const draw_match_extra_civs_4vs4 = 'nczone_draw_match_extra_civs_4vs4';
    public const draw_team_extra_civs_1vs1 = 'nczone_draw_team_extra_civs_1vs1';
    public const draw_team_extra_civs_2vs2 = 'nczone_draw_team_extra_civs_2vs2';
    public const draw_team_extra_civs_3vs3 = 'nczone_draw_team_extra_civs_3vs3';
    public const draw_team_extra_civs_4vs4 = 'nczone_draw_team_extra_civs_4vs4';
    public const draw_player_num_civs_1vs1 = 'nczone_draw_player_num_civs_1vs1';
    public const draw_player_num_civs_2vs2 = 'nczone_draw_player_num_civs_2vs2';
    public const draw_player_num_civs_3vs3 = 'nczone_draw_player_num_civs_3vs3';
    public const draw_player_num_civs_4vs4 = 'nczone_draw_player_num_civs_4vs4';
    public const draw_factor = 'nczone_draw_factor';
    public const map_images_path = 'maps/';

    public const ACL_CONFIG_KEYS = [
        self::rules_post_id,
        self::match_forum_id,
        self::pmatches_page_size,
        self::activity_time,
        self::activity_1,
        self::activity_2,
        self::activity_3,
        self::activity_4,
        self::activity_5,
        self::draw_time,
        self::bet_time,
        self::points_1vs1,
        self::points_2vs2,
        self::points_3vs3,
        self::points_4vs4,
        self::extra_points,
        self::info_posts,
        self::draw_player_civs,
        self::draw_team_civs,
        self::draw_match_extra_civs_1vs1,
        self::draw_match_extra_civs_2vs2,
        self::draw_match_extra_civs_3vs3,
        self::draw_match_extra_civs_4vs4,
        self::draw_team_extra_civs_1vs1,
        self::draw_team_extra_civs_2vs2,
        self::draw_team_extra_civs_3vs3,
        self::draw_team_extra_civs_4vs4,
        self::draw_player_num_civs_1vs1,
        self::draw_player_num_civs_2vs2,
        self::draw_player_num_civs_3vs3,
        self::draw_player_num_civs_4vs4,
        self::draw_factor,
    ];

    private static $defaults = [
        self::activity_cron => 0,
        self::rules_post_id => 0,
        self::match_forum_id => 0,
        self::pmatches_page_size => 10,
        self::activity_time => 56,
        self::activity_1 => 14,
        self::activity_2 => 28,
        self::activity_3 => 56,
        self::activity_4 => 112,
        self::activity_5 => 224,
        self::draw_time => 60,
        self::bet_time => 1200,
        self::points_1vs1 => -1,
        self::points_2vs2 => 6,
        self::points_3vs3 => 12,
        self::points_4vs4 => 9,
        self::extra_points => 30,
        self::info_posts => '',
        self::draw_player_civs => 600,
        self::draw_team_civs => 120,
        self::draw_match_extra_civs_1vs1 => 7,
        self::draw_match_extra_civs_2vs2 => 6,
        self::draw_match_extra_civs_3vs3 => 5,
        self::draw_match_extra_civs_4vs4 => 4,
        self::draw_team_extra_civs_1vs1 => 5,
        self::draw_team_extra_civs_2vs2 => 4,
        self::draw_team_extra_civs_3vs3 => 3,
        self::draw_team_extra_civs_4vs4 => 2,
        self::draw_player_num_civs_1vs1 => 9,
        self::draw_player_num_civs_2vs2 => 5,
        self::draw_player_num_civs_3vs3 => 4,
        self::draw_player_num_civs_4vs4 => 3,
        self::draw_factor => 0.4,
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
        return self::$defaults[$key] ?? $default;
    }

    # todo: try to solve this with dependency injection instead
    private static function _config(): \phpbb\config\config
    {
        return $GLOBALS['config'];
    }

    public static function module_data(array $keys): array
    {
        return \array_map(function ($key) {
            return ['config.add', [$key, self::default($key)]];
        }, $keys);
    }

    public static function draw_match_extra_civs(int $match_size)
    {
        switch ($match_size) {
            case 1: return self::get(self::draw_match_extra_civs_1vs1);
            case 2: return self::get(self::draw_match_extra_civs_2vs2);
            case 3: return self::get(self::draw_match_extra_civs_3vs3);
            case 4: return self::get(self::draw_match_extra_civs_4vs4);
        }
        return null;
    }

    public static function draw_team_extra_civs(int $match_size)
    {
        switch ($match_size) {
            case 1: return self::get(self::draw_team_extra_civs_1vs1);
            case 2: return self::get(self::draw_team_extra_civs_2vs2);
            case 3: return self::get(self::draw_team_extra_civs_3vs3);
            case 4: return self::get(self::draw_team_extra_civs_4vs4);
        }
        return null;
    }

    public static function draw_player_num_civs(int $match_size)
    {
        switch ($match_size) {
            case 1: return self::get(self::draw_player_num_civs_1vs1);
            case 2: return self::get(self::draw_player_num_civs_2vs2);
            case 3: return self::get(self::draw_player_num_civs_3vs3);
            case 4: return self::get(self::draw_player_num_civs_4vs4);
        }
        return null;
    }

    public static function base_points_by_match_size(int $match_size): int
    {
        switch ($match_size) {
            case 1: return (int) self::get(self::points_1vs1);
            case 2: return (int) self::get(self::points_2vs2);
            case 3: return (int) self::get(self::points_3vs3);
            case 4: return (int) self::get(self::points_4vs4);
        }
        return -1;
    }

    public static function activity_by_match_count(int $match_count): int
    {
        if ($match_count >= (int) self::get(self::activity_5)) {
            return 5;
        }

        if ($match_count >= (int) self::get(self::activity_4)) {
            return 4;
        }

        if ($match_count >= (int) self::get(self::activity_3)) {
            return 3;
        }

        if ($match_count >= (int) self::get(self::activity_2)) {
            return 2;
        }

        if($match_count >= (int) self::get(self::activity_1)) {
            return 1;
        }

        return 0;
    }
}
