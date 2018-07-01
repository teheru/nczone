<?php

namespace eru\nczone\zone;

class draw_setting
{
    // ID of the user who draws the match
    /** @var int */
    private $draw_user_id = 0;
    // Array of the players in team 1
    /** @var match_players_list */
    private $team1;
    // Array of the players in team 2
    /** @var match_players_list */
    private $team2;
    /** @var int */
    private $map_id = 0;
    /** @var array */
    private $match_civ_ids = [];
    /** @var array */
    private $team1_civ_ids = [];
    /** @var array */
    private $team2_civ_ids = [];
    /** @var array */
    private $player_civ_ids = [];

    public function set_draw_user_id(int $draw_user_id)
    {
        $this->draw_user_id = $draw_user_id;
    }

    public function set_team1(match_players_list $team1)
    {
        $this->team1 = $team1;
    }

    public function set_team2(match_players_list $team2)
    {
        $this->team2 = $team2;
    }

    public function set_map_id(int $set_map_id)
    {
        $this->map_id = $set_map_id;
    }

    public function set_match_civ_ids(array $set_match_civ_ids)
    {
        $this->match_civ_ids = $set_match_civ_ids;
    }

    public function set_team1_civ_ids(array $set_team1_civ_ids)
    {
        $this->team1_civ_ids = $set_team1_civ_ids;
    }

    public function set_team2_civ_ids(array $set_team2_civ_ids)
    {
        $this->team2_civ_ids = $set_team2_civ_ids;
    }

    public function set_player_civ_ids(array $set_player_civ_ids)
    {
        $this->player_civ_ids = $set_player_civ_ids;
    }

    public function get_draw_user_id(): int
    {
        return $this->draw_user_id;
    }

    public function get_team1(): match_players_list
    {
        return $this->team1;
    }

    public function get_team2(): match_players_list
    {
        return $this->team2;
    }

    public function get_map_id(): int
    {
        return $this->map_id;
    }

    public function get_match_civ_ids(): array
    {
        return $this->match_civ_ids;
    }

    public function get_team1_civ_ids(): array
    {
        return $this->team1_civ_ids;
    }

    public function get_team2_civ_ids(): array
    {
        return $this->team2_civ_ids;
    }

    public function get_player_civ_ids(): array
    {
        return $this->player_civ_ids;
    }
}
