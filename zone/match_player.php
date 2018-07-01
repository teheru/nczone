<?php

namespace eru\nczone\zone;

class match_player
{
    private $id = 0;
    private $rating = 0;

    public function __construct(int $id, int $rating)
    {
        $this->id = $id;
        $this->rating = $rating;
    }

    public function get_id(): int
    {
        return $this->id;
    }

    public function get_rating(): int
    {
        return $this->rating;
    }
}
