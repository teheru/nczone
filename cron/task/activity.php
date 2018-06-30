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

/**
 * nC Zone cron task.
 */
class activity extends \phpbb\cron\task\base
{
	/**
	 * How often we run the cron (in seconds).
	 * @var int
	 */
	protected $cron_frequency = 12*60*60;

	/**
	 * Runs this cron task.
	 *
	 * @return void
	 */
	public function run()
	{
		zone_util::players()->calculate_all_activities();
		config::set('nczone_activity_cron', time(), false);
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
		return (int)config::get('nczone_activity_cron') < time() - $this->cron_frequency;
	}
}
