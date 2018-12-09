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

    public static function create_by_row_finished(array $row): match
    {
        return self::_create_by_row($row, true);
    }

    public static function create_by_row_unfinished(array $row): match
    {
        return self::_create_by_row($row, false);
    }

    private static function _create_by_row(array $row, $finished): match
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
        $entity->map = empty($map_id) ? null : [
            'id' => $map_id,
            'title' => $row['map_name'],
        ];
        $entity->civs = array_merge(['both' => zone_util::matches()->get_match_civs($match_id, $map_id)], zone_util::matches()->get_team_civs($team1_id, $team2_id, $map_id));
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
            'forum_topic_link' => generate_board_url() . '/viewtopic.php?t=' . $this->forum_topic_id,
            'drawer' => $this->drawer,
            'map' => $this->map,
            'civs' => $this->civs,
            'bets' => $this->bets,
            'players' => $this->players,
        ];
    }
}
