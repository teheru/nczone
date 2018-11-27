<?php

namespace eru\nczone\zone\entity;

class map_civ
{
    /** @var int */
    private $map_id = 0;

    /** @var int */
    private $civ_id = 0;

    /** @var float */
    private $multiplier = 0.0;

    /** @var bool */
    private $force_draw = false;

    /** @var bool */
    private $prevent_draw = false;

    /** @var bool */
    private $both_teams = false;

    public static function create_by_row(array $row): map_civ
    {
        $entity = new self;
        $entity->map_id = (int) $row['map_id'];
        $entity->civ_id = (int) $row['civ_id'];
        $entity->multiplier = (float) ($row['multiplier'] ?? 0.0);
        $entity->force_draw = (bool) ($row['force_draw'] ?? false);
        $entity->prevent_draw = (bool) ($row['prevent_draw'] ?? false);
        $entity->both_teams = (bool) ($row['both_teams'] ?? false);
        return $entity;
    }

    public function get_map_id(): int
    {
        return $this->map_id;
    }

    public function set_map_id(int $map_id): void
    {
        $this->map_id = $map_id;
    }

    public function get_civ_id(): int
    {
        return $this->civ_id;
    }

    public function get_multiplier(): float
    {
        return $this->multiplier;
    }

    public function get_force_draw(): bool
    {
        return $this->force_draw;
    }

    public function get_prevent_draw(): bool
    {
        return $this->prevent_draw;
    }

    public function get_both_teams(): bool
    {
        return $this->both_teams;
    }

    public function as_sql_array(): array
    {
        return [
            'map_id' => $this->map_id,
            'civ_id' => $this->civ_id,
            'multiplier' => $this->multiplier,
            'force_draw' => $this->force_draw,
            'prevent_draw' => $this->prevent_draw,
            'both_teams' => $this->both_teams,
        ];
    }
}
