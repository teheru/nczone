<?php
/**
 *
 * nC Zone. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Marian Cepok, https://new-chapter.eu
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eru\nczone\migrations;

class install_db_create extends \phpbb\db\migration\migration
{
    static public function depends_on()
    {
        return ['\phpbb\db\migration\data\v31x\v314'];
    }

    public function update_schema()
    {
        return [
            'add_tables' => [
                $this->table_prefix . 'zone_players' => [
                    'COLUMNS' => [
                        'user_id' => ['UINT', null],
                        'rating' => ['UINT:8', 0],
                        'logged_in' => ['TIMESTAMP', 0],
                        'matches_won' => ['UINT', 0],
                        'matches_loss' => ['UINT', 0],
                        'bets_won' => ['UINT', 0],
                        'bets_loss' => ['UINT', 0],
                        'activity' => ['UINT:3', 0],
                    ],
                    'PRIMARY_KEY' => 'user_id',
                ],
                $this->table_prefix . 'zone_matches' => [
                    'COLUMNS' => [
                        'match_id' => ['UINT', null, 'auto_increment'],
                        'map_id' => ['UINT', 0],
                        'draw_user_id' => ['UINT', 0],
                        'post_user_id' => ['UINT', 0],
                        'draw_time' => ['TIMESTAMP', 0],
                        'post_time' => ['TIMESTAMP', 0],
                        'winner_team_id' => ['UINT', 0],
                        'forum_topic_id' => ['UINT', 0],
                    ],
                    'PRIMARY_KEY' => 'match_id',
                    'KEYS' => [
                        'map_id' => ['INDEX', 'map_id'],
                        'draw_uid' => ['INDEX', 'draw_user_id'],
                        'post_uid' => ['INDEX', 'post_user_id'],
                        'post_tid' => ['INDEX', 'forum_topic_id'],
                    ],
                ],
                $this->table_prefix . 'zone_match_teams' => [
                    'COLUMNS' => [
                        'team_id' => ['UINT', null, 'auto_increment'],
                        'match_id' => ['UINT', 0],
                        'match_team' => ['UINT:2', 0],
                    ],
                    'PRIMARY_KEY' => 'team_id',
                    'KEYS' => [
                        'mid' => ['INDEX', 'match_id'],
                    ],
                ],
                $this->table_prefix . 'zone_dreamteams' => [
                    'COLUMNS' => [
                        'user1_id' => ['UINT', 0],
                        'user2_id' => ['UINT', 0],
                        'matches_won' => ['UINT', 0],
                        'matches_loss' => ['UINT', 0],
                    ],
                    'PRIMARY_KEY' => ['user1_id', 'user2_id'],
                ],
                $this->table_prefix . 'zone_match_players' => [
                    'COLUMNS' => [
                        'team_id' => ['UINT', 0],
                        'user_id' => ['UINT', 0],
                        'draw_rating' => ['UINT:8', 0],
                        'rating_change' => ['INT:8', 0],
                        'streak' => ['INT:4', 0],
                    ],
                    'PRIMARY_KEY' => ['team_id', 'user_id'],
                ],
                $this->table_prefix . 'zone_civs' => [
                    'COLUMNS' => [
                        'civ_id' => ['UINT', null, 'auto_increment'],
                        'civ_name' => ['VCHAR:48', ''],
                    ],
                    'PRIMARY_KEY' => 'civ_id',
                ],
                $this->table_prefix . 'zone_maps' => [
                    'COLUMNS' => [
                        'map_id' => ['UINT', null, 'auto_increment'],
                        'map_name' => ['VCHAR:48', ''],
                        'weight' => ['DECIMAL', 0.0],
                        'description' => ['TEXT', ''],
                    ],
                    'PRIMARY_KEY' => 'map_id',
                ],
                $this->table_prefix . 'zone_map_civs' => [
                    'COLUMNS' => [
                        'map_id' => ['UINT', 0],
                        'civ_id' => ['UINT', 0],
                        'multiplier' => ['DECIMAL', 0.0],
                        'force_draw' => ['BOOL', 0],
                        'prevent_draw' => ['BOOL', 0],
                        'both_teams' => ['BOOL', 0],
                    ],
                    'PRIMARY_KEY' => ['map_id', 'civ_id'],
                ],
                $this->table_prefix . 'zone_match_civs' => [
                    'COLUMNS' => [
                        'match_id' => ['UINT', 0],
                        'civ_id' => ['UINT', 0],
                        'number' => ['UINT:3', 0],
                    ],
                    'PRIMARY_KEY' => ['match_id', 'civ_id'],
                ],
                $this->table_prefix . 'zone_match_team_civs' => [
                    'COLUMNS' => [
                        'team_id' => ['UINT', 0],
                        'civ_id' => ['UINT', 0],
                        'number' => ['UINT:3', 0],
                    ],
                    'PRIMARY_KEY' => ['team_id', 'civ_id'],
                ],
                $this->table_prefix . 'zone_match_player_civs' => [
                    'COLUMNS' => [
                        'match_id' => ['UINT', 0],
                        'user_id' => ['UINT', 0],
                        'civ_id' => ['UINT', 0],
                    ],
                    'PRIMARY_KEY' => ['match_id', 'user_id'],
                ],
                $this->table_prefix . 'zone_player_map' => [
                    'COLUMNS' => [
                        'user_id' => ['UINT', 0],
                        'map_id' => ['UINT', 0],
                        'time' => ['TIMESTAMP', 0],
                    ],
                    'PRIMARY_KEY' => ['user_id', 'map_id'],
                ],
                $this->table_prefix . 'zone_player_civ' => [
                    'COLUMNS' => [
                        'user_id' => ['UINT', 0],
                        'civ_id' => ['UINT', 0],
                        'time' => ['TIMESTAMP', 0],
                    ],
                    'PRIMARY_KEY' => ['user_id', 'civ_id'],
                ],
                $this->table_prefix . 'zone_bets' => [
                    'COLUMNS' => [
                        'user_id' => ['UINT', 0],
                        'team_id' => ['UINT', 0],
                        'time' => ['TIMESTAMP', 0],
                        'counted' => ['UINT:1', 0],
                    ],
                    'PRIMARY_KEY' => ['user_id', 'team_id'],
                ],
                $this->table_prefix . 'zone_draw_process' => [
                    'COLUMNS' => [
                        'draw_id' => ['UINT', null, 'auto_increment'],
                        'user_id' => ['UINT', 0],
                        'time' => ['TIMESTAMP', 0],
                    ],
                    'PRIMARY_KEY' => 'draw_id',
                    'KEYS' => [
                        'uid' => ['INDEX', 'user_id'],
                    ],
                ],
                $this->table_prefix . 'zone_draw_players' => [
                    'COLUMNS' => [
                        'draw_id' => ['UINT', 0],
                        'user_id' => ['UINT', 0],
                        'logged_in' => ['TIMESTAMP', 0],
                    ],
                    'PRIMARY_KEY' => ['draw_id', 'user_id'],
                ],
            ],
        ];
    }

    public function revert_schema()
    {
        return [
            'drop_tables' => [
                $this->table_prefix . 'zone_players',
                $this->table_prefix . 'zone_matches',
                $this->table_prefix . 'zone_match_players',
                $this->table_prefix . 'zone_civs',
                $this->table_prefix . 'zone_maps',
                $this->table_prefix . 'zone_map_civs',
                $this->table_prefix . 'zone_match_civs',
                $this->table_prefix . 'zone_match_team_civs',
                $this->table_prefix . 'zone_match_player_civs',
                $this->table_prefix . 'zone_player_map',
                $this->table_prefix . 'zone_player_civ',
                $this->table_prefix . 'zone_bets',
                $this->table_prefix . 'zone_draw_process',
                $this->table_prefix . 'zone_draw_players',
            ],
        ];
    }
}
