<?php

namespace eru\nczone\zone;

class player implements \JsonSerializable
{
    /** @var int */
    private $id = 0;
    /** @var string */
    private $username = '';
    /** @var int */
    private $rating = 0;
    /** @var int */
    private $logged_in = 0;
    /** @var int */
    private $last_activity = 0;
    /** @var int */
    private $wins = 0;
    /** @var int */
    private $losses = 0;
    /** @var int */
    private $ratingchange = 0;
    /** @var int */
    private $streak = 0;
    /** @var int */
    private $bets_won = 0;
    /** @var int */
    private $bets_loss = 0/** @var int */
    ;
    private $activity = 0;

    public static function create_by_row(array $row): player
    {
        $p = new self;
        $p->id = (int)$row['id'];
        $p->username = (string)$row['username'];
        $p->rating = (int)$row['rating'];
        $p->logged_in = (int)$row['logged_in'];
        $p->last_activity = (int)$row['last_activity'];
        $p->wins = (int)$row['matches_won'];
        $p->losses = (int)$row['matches_loss'];
        $p->ratingchange = (int)$row['rating_change'];
        $p->streak = (int)$row['streak'];
        $p->bets_won = (int)$row['bets_won'];
        $p->bets_loss = (int)$row['bets_loss'];
        $p->activity = (int)$row['activity'];
        return $p;
    }

    public function is_activated(): bool
    {
        return $this->id && $this->rating;
    }

    public function get_id(): int
    {
        return $this->id;
    }

    public function get_username(): string
    {
        return $this->username;
    }

    public function get_rating(): int
    {
        return $this->rating;
    }

    public function get_logged_in(): int
    {
        return $this->logged_in;
    }

    private function get_games_count(): int
    {
        return $this->wins + $this->losses;
    }

    private function get_winrate(): float
    {
        return $this->get_games_count() ? $this->wins / $this->get_games_count() * 100 : 0;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'rating' => $this->rating,
            'logged_in' => $this->logged_in,
            'last_activity' => max($this->logged_in, $this->last_activity),
            'games' => $this->get_games_count(),
            'wins' => $this->wins,
            'losses' => $this->losses,
            'winrate' => $this->get_winrate(),
            'ratingchange' => $this->ratingchange,
            'streak' => $this->streak,
            'bets_won' => $this->bets_won,
            'bets_loss' => $this->bets_loss,
            'activity' => $this->activity,
        ];
    }
}
