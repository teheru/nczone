<?php

namespace eru\nczone\zone;

use eru\nczone\utility\number_util;

class match_players_list
{
    private $items = [];

    public function __construct(array...$arrays)
    {
        foreach ($arrays as $array) {
            foreach ($array as $item) {
                $this->items[] = $item;
            }
        }
    }

    // todo: work with player objects instead of array
    // the items put in here look like players, but the only things that are surely defined are `id` and `rating`
    // ['id' => (int), 'rating' => (int), ...]
    public function add(array $p): void
    {
        $this->items[] = $p;
    }

    public function contains_id(int $id): bool
    {
        foreach ($this->items as $p) {
            if ($p['id'] === $id) {
                return true;
            }
        }
        return false;
    }

    public function remove_by_id(int $id): void
    {
        $items = [];
        foreach ($this->items as $p) {
            if ($p['id'] !== $id) {
                $items[] = $p;
            }
        }
        $this->items = $items;
    }

    public function items(): array
    {
        return $this->items;
    }

    public function length(): int
    {
        return \count($this->items);
    }

    public function unshift(array $p): void
    {
        array_unshift($this->items, $p);
    }

    public function pop(): ?array
    {
        return array_pop($this->items);
    }

    public function slice($offset, $size): match_players_list
    {
        $list = new match_players_list;
        foreach (\array_slice($this->items, $offset, $size) as $p) {
            $list->add($p);
        }
        return $list;
    }

    public function sorted_by_rating(): match_players_list
    {
        return $this->sorted_by('rating');
    }

    private function sorted_by(string $prop): match_players_list
    {
        $list = new match_players_list;
        foreach (self::sort($this->items, $prop) as $p) {
            $list->add($p);
        }
        return $list;
    }

    // only works for numeric props for now
    private static function sort(array $players, $prop): array
    {
        usort($players, function ($p1, $p2) use ($prop) {
            return number_util::cmp($p1[$prop], $p2[$prop]);
        });
        return $players;
    }
}
