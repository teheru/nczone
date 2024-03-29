<?php

namespace eru\nczone\config;

final class config
{
    public const activity_cron = 'nczone_activity_cron';

    public const CRON_CONFIG_KEYS = [
        self::activity_cron,
    ];

    public const title = 'nczone_title';
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
    public const free_points = 'nczone_free_points';
    public const free_points_regular = 'nczone_free_points_regular';
    public const free_points_unrated = 'nczone_free_points_unrated';
    public const free_points_difference = 'nczone_free_points_difference';
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
    public const draw_match_additional_civs = 'nczone_draw_match_additional_civs';
    public const draw_team_additional_civs = 'nczone_draw_team_additional_civs';
    public const draw_player_additional_civs = 'nczone_draw_player_additional_civs';
    public const draw_player_additional_civs_max_multiplier = 'nczone_draw_player_additional_civs_max_multiplier';
    public const draw_factor = 'nczone_draw_factor';
    public const draw_switch_1_player = 'nczone_draw_switch_1_player';
    public const draw_switch_0_players = 'nczone_draw_switch_0_players';
    public const free_pick_civ_id = 'nczone_free_pick_civ_id';
    public const number_map_vetos = 'nczone_number_map_vetos';
    public const map_veto_power = 'nczone_map_veto_power';
    public const draw_block_time = 'nczone_draw_block_time';
    public const draw_blocked_until = 'nczone_draw_blocked_until';
    public const draw_block_after_match = 'nczone_draw_block_after_match';
    public const map_images_path = 'maps/';

    public const ACL_CONFIG_KEYS = [
        self::title,
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
        self::free_points,
        self::free_points_regular,
        self::free_points_unrated,
        self::free_points_difference,
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
        self::draw_match_additional_civs,
        self::draw_team_additional_civs,
        self::draw_player_additional_civs,
        self::draw_player_additional_civs_max_multiplier,
        self::draw_factor,
        self::draw_switch_1_player,
        self::draw_switch_0_players,
        self::free_pick_civ_id,
        self::number_map_vetos,
        self::map_veto_power,
        self::draw_block_time,
        self::draw_blocked_until,
        self::draw_block_after_match,
    ];

    private static $defaults = [
        self::title => 'Zone',
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
        self::free_points => 1,
        self::free_points_regular => true,
        self::free_points_unrated => false,
        self::free_points_difference => false,
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
        self::draw_match_additional_civs => 1,
        self::draw_team_additional_civs => 1,
        self::draw_player_additional_civs => 0,
        self::draw_player_additional_civs_max_multiplier => 2.0,
        self::draw_factor => 0.4,
        self::draw_switch_1_player => 4,
        self::draw_switch_0_players => 10,
        self::free_pick_civ_id => 0,
        self::number_map_vetos => 0,
        self::map_veto_power => 2.0,
        self::draw_block_time => 10,
        self::draw_blocked_until => 0,
        self::draw_block_after_match => 3,
    ];

    /** @var \phpbb\config\config */
    private $config;

    public function __construct(\phpbb\config\config $config)
    {
        $this->config = $config;
    }

    public function get(string $key, $default = null)
    {
        return $this->config[$key] ?? self::default($key, $default);
    }

    public function set(string $key, $value, $use_cache = true)
    {
        return $this->config->set($key, $value, $use_cache);
    }

    public static function default(string $key, $default = null)
    {
        return self::$defaults[$key] ?? $default;
    }

    public static function module_data(array $keys): array
    {
        return \array_map(static function ($key) {
            return ['config.add', [$key, self::default($key)]];
        }, $keys);
    }

    public function draw_match_extra_civs(int $match_size): int
    {
        switch ($match_size) {
            case 1: return (int) $this->get(self::draw_match_extra_civs_1vs1);
            case 2: return (int) $this->get(self::draw_match_extra_civs_2vs2);
            case 3: return (int) $this->get(self::draw_match_extra_civs_3vs3);
            case 4: return (int) $this->get(self::draw_match_extra_civs_4vs4);
        }
        return 0;
    }

    public function draw_team_extra_civs(int $match_size): int
    {
        switch ($match_size) {
            case 1: return (int) $this->get(self::draw_team_extra_civs_1vs1);
            case 2: return (int) $this->get(self::draw_team_extra_civs_2vs2);
            case 3: return (int) $this->get(self::draw_team_extra_civs_3vs3);
            case 4: return (int) $this->get(self::draw_team_extra_civs_4vs4);
        }
        return 0;
    }

    public function draw_player_num_civs(int $match_size): int
    {
        switch ($match_size) {
            case 1: return (int) $this->get(self::draw_player_num_civs_1vs1);
            case 2: return (int) $this->get(self::draw_player_num_civs_2vs2);
            case 3: return (int) $this->get(self::draw_player_num_civs_3vs3);
            case 4: return (int) $this->get(self::draw_player_num_civs_4vs4);
        }
        return 0;
    }

    public function draw_match_additional_civs(): int
    {
        return (int) $this->get(self::draw_match_additional_civs);
    }

    public function draw_team_additional_civs(): int
    {
        return (int) $this->get(self::draw_team_additional_civs);
    }

    public function draw_player_additional_civs(): int
    {
        return (int) $this->get(self::draw_player_additional_civs);
    }

    public function draw_player_additional_civs_max_multiplier(): float
    {
        return (float) $this->get(self::draw_player_additional_civs_max_multiplier);
    }

    public function base_points_by_match_size(int $match_size): int
    {
        switch ($match_size) {
            case 1: return (int) $this->get(self::points_1vs1);
            case 2: return (int) $this->get(self::points_2vs2);
            case 3: return (int) $this->get(self::points_3vs3);
            case 4: return (int) $this->get(self::points_4vs4);
        }
        return -1;
    }

    public function activity_by_match_count(int $match_count): int
    {
        if ($match_count >= (int) $this->get(self::activity_5)) {
            return 5;
        }

        if ($match_count >= (int) $this->get(self::activity_4)) {
            return 4;
        }

        if ($match_count >= (int) $this->get(self::activity_3)) {
            return 3;
        }

        if ($match_count >= (int) $this->get(self::activity_2)) {
            return 2;
        }

        if($match_count >= (int) $this->get(self::activity_1)) {
            return 1;
        }

        return 0;
    }
}
