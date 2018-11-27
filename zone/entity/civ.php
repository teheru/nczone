<?php

namespace eru\nczone\zone\entity;

class civ
{
    /** @var int */
    private $id = 0;
    /** @var string */
    private $name = '';

    public static function create_by_row(array $row): civ
    {
        $civ = new self;
        $civ->id = (int)$row['id'];
        $civ->name = (string)$row['name'];
        return $civ;
    }

    public function get_id(): int
    {
        return $this->id;
    }

    public function get_name(): string
    {
        return $this->name;
    }
}
