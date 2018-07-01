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
        $item = $this->items;
        usort($item, function (match_player $p1, match_player $p2) {
            return number_util::cmp($p1->get_rating(), $p2->get_rating());
        });
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
}
