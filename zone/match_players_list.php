<?php

namespace eru\nczone\zone;

use eru\nczone\utility\number_util;

class match_players_list
{
    /** @var match_player[] */
    private $items = [];

    public function add(match_player $p): void
    {
        $this->items[] = $p;
    }

    public function get_by_id(int $id): ?match_player
    {
        foreach ($this->items as $p) {
            if ($p->get_id() === $id) {
                return $p;
            }
        }
        return null;
    }

    public function contains_id(int $id): bool
    {
        foreach ($this->items as $p) {
            if ($p->get_id() === $id) {
                return true;
            }
        }
        return false;
    }

    public function remove_by_id(int $id): void
    {
        $items = [];
        foreach ($this->items as $p) {
            if ($p->get_id() !== $id) {
                $items[] = $p;
            }
        }
        $this->items = $items;
    }

    /**
     * @return match_player[]
     */
    public function items(): array
    {
        return $this->items;
    }

    public function length(): int
    {
        return \count($this->items);
    }

    public function unshift(match_player $p): void
    {
        array_unshift($this->items, $p);
    }

    public function pop(): ?match_player
    {
        return array_pop($this->items);
    }

    public function pick_indexes(int ...$indexes): match_players_list
    {
        $list = new self;
        foreach ($indexes as $index) {
            $list->add($this->items[$index]);
        }
        return $list;
    }

    public function slice($offset, $size): match_players_list
    {
        $list = new self;
        foreach (\array_slice($this->items, $offset, $size) as $p) {
            $list->add($p);
        }
        return $list;
    }

    public function sorted_by_rating(): match_players_list
    {
        $list = new self;
        $items = $this->items;
        usort($items, function (match_player $p1, match_player $p2) {
            return number_util::cmp($p1->get_rating(), $p2->get_rating());
        });
        foreach ($items as $item) {
            $list->add($item);
        }
        return $list;
    }

    /**
     * @return int[]
     */
    public function get_ids(): array
    {
        return array_map(function (match_player $p) {
            return $p->get_id();
        }, $this->items);
    }

    public function get_total_rating(): int
    {
        return array_reduce($this->items, function ($p1, match_player $p2) {
            return $p1 + $p2->get_rating();
        }, 0);
    }

    public function get_rating_difference(match_players_list $team2): int
    {
        return $this->get_total_rating() - $team2->get_total_rating();
    }

    public function get_abs_rating_difference(match_players_list $team2): int
    {
        return abs($this->get_rating_difference($team2));
    }

    public function get_min_max_diff(match_players_list $team2)
    {
        return abs(self::get_max_rating_of_all($this, $team2) - self::get_min_rating_of_all($this, $team2));
    }

    private function get_max_rating(): int
    {
        $max = PHP_INT_MIN;
        foreach ($this->items as $player) {
            if ($player->get_rating() > $max) {
                $max = $player->get_rating();
            }
        }
        return $max === PHP_INT_MIN ? 0 : $max;
    }

    private function get_min_rating(): int
    {
        $min = PHP_INT_MAX;
        foreach ($this->items as $player) {
            if ($player->get_rating() < $min) {
                $min = $player->get_rating();
            }
        }
        return $min === PHP_INT_MAX ? 0 : $min;
    }

    private static function get_max_rating_of_all(match_players_list ...$players_lists): int
    {
        return max(...array_map(function(match_players_list $list) {
            return $list->get_max_rating();
        }, $players_lists));
    }

    private static function get_min_rating_of_all(match_players_list ...$players_lists): int
    {
        return min(...array_map(function(match_players_list $list) {
            return $list->get_min_rating();
        }, $players_lists));
    }
}
