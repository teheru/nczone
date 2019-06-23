<?php
/**
 *
 * nC Zone. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Marian Cepok, https://new-chapter.eu
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eru\nczone\cron\task;

use eru\nczone\config\config;
use eru\nczone\utility\zone_util;
use eru\nczone\zone\players;

/**
 * nC Zone cron task.
 */
class activity extends \phpbb\cron\task\base
{
    /** @var config */
    private $config;

    /** @var players */
    private $players;

    /**
     * How often we run the cron (in seconds).
     * @var int
     */
    protected $cron_frequency = 12 * 60 * 60;

    public function __construct()
    {
        // TODO: provide via dependency injection
        $this->config = zone_util::config();
        $this->players = zone_util::players();
    }

    /**
     * Runs this cron task.
     *
     * @return void
     */
    public function run()
    {
        $this->players->calculate_all_activities();
        $this->config->set(config::activity_cron, time(), false);
    }

    /**
     * Returns whether this cron task can run, yes it can
     *
     * @return bool
     */
    public function is_runnable()
    {
        return true;
    }

    /**
     * Returns whether this cron task should run now, because enough time
     * has passed since it was last run.
     *
     * @return bool
     */
    public function should_run()
    {
        return (int) $this->config->get(config::activity_cron) < time() - $this->cron_frequency;
    }
}
