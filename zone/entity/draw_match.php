<?php

namespace eru\nczone\zone\entity;

class draw_match
{
    /** @var match_players_list */
    private $team1;

    /** @var match_players_list */
    private $team2;

    public function __construct(
        match_players_list $team1,
        match_players_list $team2
    ) {
        $this->team1 = $team1;
        $this->team2 = $team2;
    }

    public function get_team1(): match_players_list
    {
        return $this->team1;
    }

    public function get_team2(): match_players_list
    {
        return $this->team2;
    }

    public function get_abs_rating_difference(): int
    {
        return $this->team1->get_abs_rating_difference($this->team2);
    }

    public function get_match_size(): int
    {
        return $this->team1->length();
    }

    public function get_min_max_diff()
    {
        return $this->team1->get_min_max_diff($this->team2);
    }

    public function get_all_player_ids(): array
    {
        return \array_merge(
            $this->team1->get_ids(),
            $this->team2->get_ids()
        );
    }
}
