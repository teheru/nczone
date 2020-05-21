<?php
/**
 *
 * nC Zone. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Marian Cepok, https://new-chapter.eu
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eru\nczone\event;

/**
 * @ignore
 */

use phpbb\language\language;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * nC Zone Event listener.
 */
class acp_listener implements EventSubscriberInterface
{
    static public function getSubscribedEvents()
	{
		return array(
			'core.get_logs_main_query_before' => 'load_acp_language',
		);
	}

	/* @var \phpbb\controller\helper */
	protected $lang;

	/**
	 * Constructor
	 *
	 * @param \phpbb\controller\helper	$helper		Controller helper object
	 * @param \phpbb\template\template	$template	Template object
	 * @param \phpbb\user               $user       User object
	 * @param string                    $php_ext    phpEx
	 */
	public function __construct(language $lang)
	{
		$this->lang = $lang;
    }

	/**
	 * Load ACP language files during user setup
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function load_acp_language($event)
	{
        $this->lang->add_lang(['info_logs'], 'eru/nczone');
	}
}
