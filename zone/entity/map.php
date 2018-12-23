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

    public static function create_by_row(array $row): map
    {
        $map = new self;
        $map->id = (int)$row['id'];
        $map->name = (string)$row['name'];
        $map->weight = (float)$row['weight'];
        $map->description = $row['description'];
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
}
