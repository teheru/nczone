<?php

namespace eru\nczone\zone\entity;

use eru\nczone\utility\number_util;

class match_players_list
{
    /** @var match_player[] */
    private $items = [];

    public static function from_match_players(
        array $players
    ): match_players_list {
        $list = new self();
        foreach ($players as $p) {
            $list->push($p);
        }
        return $list;
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

    public function push(match_player ...$players): void
    {
        array_push($this->items, ...$players);
    }

    public function pop(): ?match_player
    {
        return \array_pop($this->items);
    }

    public function unshift(match_player ...$players): void
    {
        \array_unshift($this->items, ...$players);
    }

    public function shift(): ?match_player
    {
        return \array_shift($this->items);
    }

    public function pick_indexes(int ...$indexes): match_players_list
    {
        $list = new self;
        foreach ($indexes as $index) {
            $list->push($this->items[$index]);
        }
        return $list;
    }

    public function slice($offset, $size): match_players_list
    {
        $list = new self;
        foreach (\array_slice($this->items, $offset, $size) as $p) {
            $list->push($p);
        }
        return $list;
    }

    public function sorted_by_rating(): match_players_list
    {
        $list = new self;
        $items = $this->items;
        \usort($items, function (match_player $p1, match_player $p2) {
            return number_util::cmp($p1->get_rating(), $p2->get_rating());
        });
        foreach ($items as $item) {
            $list->push($item);
        }
        return $list;
    }

    /**
     * @return int[]
     */
    public function get_ids(): array
    {
        return \array_map(function (match_player $p) {
            return $p->get_id();
        }, $this->items);
    }

    public function get_total_rating(): int
    {
        return \array_reduce($this->items, function ($rating, match_player $player) {
            return $rating + $player->get_rating();
        }, 0);
    }

    public function get_total_rating_with_multiplier_map(array $multiplier_map): int
    {
        return \array_reduce($this->items, function ($rating, match_player $player) use ($multiplier_map) {
            return $rating + $player->get_rating() * ($multiplier_map[$player->get_id()]['multiplier'] ?? 0);
        }, 0);
    }

    public function get_rating_difference(match_players_list $team2): int
    {
        return $this->get_total_rating() - $team2->get_total_rating();
    }

    public function get_abs_rating_difference(match_players_list $team2): int
    {
        return number_util::diff(
            $this->get_total_rating(),
            $team2->get_total_rating()
        );
    }

    public function get_min_max_diff(match_players_list $team2)
    {
        return number_util::diff(
            self::get_max_rating_of_all($this, $team2),
            self::get_min_rating_of_all($this, $team2)
        );
    }

    public function get_max_rating(): int
    {
        return $this->get_extreme_rating('\max');
    }

    public function get_min_rating(): int
    {
        return $this->get_extreme_rating('\min');
    }

    private function get_extreme_rating(callable $extreme_finder): int
    {
        if (empty($this->items)) {
            return 0;
        }
        return $extreme_finder(\array_map(function (match_player $p) {
            return $p->get_rating();
        }, $this->items));
    }

    private static function get_max_rating_of_all(match_players_list ...$players_lists): int
    {
        return \max(...\array_map(function(match_players_list $list) {
            return $list->get_max_rating();
        }, $players_lists));
    }

    private static function get_min_rating_of_all(match_players_list ...$players_lists): int
    {
        return \min(...\array_map(function(match_players_list $list) {
            return $list->get_min_rating();
        }, $players_lists));
    }

    private static function get_mean_rating(match_players_list ...$players_lists): int
    {
        $value = 0;
        $number = 0;
        foreach ($players_lists as $list) {
            $value += $list->get_total_rating();
            $number += $list->length();
        }
        return $number === 0 ? 0 : ($value / $number);
    }

    public static function get_abs_rating_variance(match_players_list ...$players_lists): int
    {
       $mean = self::get_mean_rating(...$players_lists);
       $value = 0;
       foreach ($players_lists as $list) {
            $player_array = $list->items();
            foreach ($player_array as $player) {
                $value += \abs($mean - $player->get_rating());
            }
       }
       return $value;
    }
}
