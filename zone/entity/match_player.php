<?php

namespace eru\nczone\zone\entity;

class match_player
{
    private $id;
    private $rating;

    public function __construct(int $id, int $rating)
    {
        $this->id = $id;
        $this->rating = $rating;
    }

    /**
     * @param array $rows
     *
     * @return match_player[]
     */
    public static function array_by_rows(array $rows): array
    {
        return \array_map([__CLASS__, 'create_by_row'], $rows);
    }

    public static function create_by_player(player $p): match_player
    {
        return new self($p->get_id(), $p->get_rating());
    }

    public static function create_by_row(array $row): match_player
    {
        return new self((int)$row['id'], (int)$row['rating']);
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
