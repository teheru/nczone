<?php

/**
 *
 * nC Zone. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Marian Cepok, https://new-chapter.eu
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eru\nczone\zone;

class logs
{
    /** @var \phpbb\log\log_interface */
    private $log;

    /** @var \phpbb\user */
    private $user;

    public const PLAYER_ACTIVATED = 'NCZONE_LOG_PLAYER_ACTIVATED';
    public const PLAYER_RATING_CHANGED = 'NCZONE_LOG_RATING_CHANGED';
    public const LOGGED_IN_PLAYER = 'NCZONE_LOG_LOGGED_IN_PLAYER';
    public const LOGGED_OUT_PLAYER = 'NCZONE_LOG_LOGGED_OUT_PLAYER';
    public const DRAW_PREVIEW = 'NCZONE_LOG_DRAW_PREVIEW';
    public const DRAW_CONFIRMED = 'NCZONE_LOG_DRAW_CONFIRMED';
    public const DRAW_ABORTED = 'NCZONE_LOG_DRAW_ABORTED';
    public const POSTED_MATCH = 'NCZONE_LOG_POSTED_MATCH';
    public const LOCKED = 'NCZONE_LOG_LOCKED';
    public const UNLOCKED = 'NCZONE_LOG_UNLOCKED';
    public const REPLACE_PLAYER = 'NCZONE_LOG_REPLACED_PLAYER';
    public const ADD_PAIR = 'NCZONE_LOG_ADD_PAIR';

    public function __construct(\phpbb\log\log_interface $log, \phpbb\user $user)
    {
        $this->log = $log;
        $this->user = $user;
    }

    public function log_admin_action(string $reason, array $additional_data = []): void
    {
        $this->log_common('admin', $reason, $additional_data);
    }

    public function log_mod_action(string $reason, array $additional_data = []): void
    {
        $this->log_common('mod', $reason, $additional_data);
    }

    public function log_user_action(string $reason, array $additional_data = []): void
    {
        $this->log_common('user', $reason, $additional_data);
    }

    private function log_common(string $mode, string $reason, array $additional_data): void
    {
        $additional_data = \array_merge([$this->user->data['username']], $additional_data);
        $this->log->add($mode, $this->user->data['user_id'], $this->user->ip, $reason, false, $additional_data);
    }
}