<?php

namespace eru\nczone\zone\entity;

use eru\nczone\utility\zone_util;

class match implements \JsonSerializable
{
    private $match_id;
    private $draw_time;
    private $post_time;
    private $winner;
    private $result_poster;
    private $drawer;
    private $map;
    private $civs;
    private $bets;
    private $players;
    private $forum_topic_id;

    /**
     * @param array $rows
     * @param bool $with_description
     *
     * @return match[]
     */
    public static function array_by_rows_unfinished(array $rows, bool $with_description): array
    {
        return \array_map(static function(array $row) use ($with_description) {
            return self::create_by_row_unfinished($row, $with_description);
        }, $rows);
    }

    /**
     * @param array $rows
     * @param bool $with_description
     *
     * @return match[]
     */
    public static function array_by_rows_finished(array $rows, bool $with_description): array
    {
        return \array_map(static function(array $row) use ($with_description) {
            return self::create_by_row_finished($row, $with_description);
        }, $rows);
    }

    public static function create_by_row_finished(array $row, bool $with_description): match
    {
        return self::_create_by_row($row, true, $with_description);
    }

    public static function create_by_row_unfinished(array $row, bool $with_description): match
    {
        return self::_create_by_row($row, false, $with_description);
    }

    private static function _create_by_row(array $row, bool $finished, bool $with_description): match
    {
        $match_id = (int)$row['match_id'];
        $map_id = (int)$row['map_id'];
        $team1_id = (int)$row['team1_id'];
        $team2_id = (int)$row['team2_id'];
        $forum_topic_id = (int)$row['forum_topic_id'];

        $entity = new self();
        $entity->match_id = $match_id;
        $entity->draw_time = (int)$row['draw_time'];
        $entity->post_time = (int)($row['post_time'] ?? 0);
        $entity->winner = self::determine_winner((int)($row['winner_team_id'] ?? 0), $team1_id, $team2_id);
        $entity->result_poster = empty($row['post_user_id']) ? null : [
            'id' => (int)$row['post_user_id'],
            'username' => $row['post_username'],
        ];
        $entity->drawer = empty($row['draw_user_id']) ? null : [
            'id' => (int)$row['draw_user_id'],
            'username' => $row['draw_username'],
        ];

        $map_array = [
            'id' => $map_id,
            'name' => $row['map_name']
        ];
        if ($with_description) {
            $map_array['description'] = $row['map_description'];
            $map_array['image'] = zone_util::maps()->get_image_url($map_id);
        }

        $entity->map = empty($map_id) ? null : $map_array;
        $entity->civs = array_merge(
            [
                'both' => zone_util::matches()->get_match_civs($match_id, $map_id),
                'banned' => zone_util::matches()->get_banned_civs($match_id, $map_id),
            ],
            zone_util::matches()->get_team_civs($team1_id, $team2_id, $map_id)
        );
        $entity->bets = zone_util::bets()->get_bets($team1_id, $team2_id, $finished ? true : false);
        $entity->players = zone_util::players()->get_match_players($match_id, $team1_id, $team2_id, $map_id);
        $entity->forum_topic_id = $forum_topic_id;
        return $entity;
    }

    private static function determine_winner(
        int $winner_team_id,
        int $team1_id,
        int $team2_id
    ): int {
        if ($winner_team_id === $team1_id) {
            return 1;
        }

        if ($winner_team_id === $team2_id) {
            return 2;
        }

        return 0;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->match_id,
            'draw_time' => $this->draw_time,
            'post_time' => $this->post_time,
            'winner' => $this->winner,
            'result_poster' => $this->result_poster,
            'forum_topic_link' => append_sid(generate_board_url() . '/viewtopic.php?t=' . $this->forum_topic_id),
            'drawer' => $this->drawer,
            'map' => $this->map,
            'civs' => $this->civs,
            'bets' => $this->bets,
            'players' => $this->players,
        ];
    }
}
