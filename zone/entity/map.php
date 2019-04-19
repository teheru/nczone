<?php

namespace eru\nczone\zone\entity;

class map
{
    /** @var int */
    private $id = 0;
    /** @var string */
    private $name = '';
    /** @var float */
    private $weight = .0;
    /** @var string */
    private $description = '';
    /** @var array */
    private $match_sizes = [1 => false, 2 => false, 3 => false, 4 => false];

    public static function create_by_row(array $row): map
    {
        $map = new self;
        $map->id = (int)$row['id'];
        $map->name = (string)$row['name'];
        $map->weight = (float)$row['weight'];
        $map->description = $row['description'];
        $map->match_sizes = [
            1 => $row['draw_1vs1'],
            2 => $row['draw_2vs2'],
            3 => $row['draw_3vs3'],
            4 => $row['draw_4vs4']
        ];
        return $map;
    }

    public function get_id(): int
    {
        return $this->id;
    }

    public function get_name(): string
    {
        return $this->name;
    }

    public function get_weight(): float
    {
        return $this->weight;
    }

    public function get_description(): string
    {
        return $this->description;
    }

    public function get_match_sizes(): array
    {
        return $this->match_sizes;
    }
}
