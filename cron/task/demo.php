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

/**
 * nC Zone cron task.
 */
class demo extends \phpbb\cron\task\base
{
	/**
	 * How often we run the cron (in seconds).
	 * @var int
	 */
	protected $cron_frequency = 86400;

	/** @var \phpbb\config\config */
	protected $config;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config $config Config object
	 */
	public function __construct(\phpbb\config\config $config)
	{
		$this->config = $config;
	}

	/**
	 * Runs this cron task.
	 *
	 * @return void
	 */
	public function run()
	{
		// Run your cron actions here...

		// Update the cron task run time here if it hasn't
		// already been done by your cron actions.
		$this->config->set('acme_cron_last_run', time(), false);
	}

	/**
	 * Returns whether this cron task can run, given current board configuration.
	 *
	 * For example, a cron task that prunes forums can only run when
	 * forum pruning is enabled.
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
		return $this->config['acme_cron_last_run'] < time() - $this->cron_frequency;
	}
}
