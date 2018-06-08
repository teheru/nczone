<?php
/**
 *
 * nC Zone. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Marian Cepok, https://new-chapter.eu
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eru\nczone\controller;

/**
 * nC Zone main controller.
 */
class main
{
	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\user */
	protected $user;

	/* @var \eru\nczone\zone\players */
	protected $zone_players;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config		$config
	 * @param \phpbb\controller\helper	$helper
	 * @param \phpbb\template\template	$template
	 * @param \phpbb\user				$user
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user, \phpbb\auth\auth $auth, \eru\nczone\zone\players $zone_players)
	{
		$this->config = $config;
		$this->helper = $helper;
		$this->template = $template;
		$this->user = $user;
		$this->auth = $auth;
		$this->zone_players = $zone_players;
	}

	/**
	 * Demo controller for route /demo/{name}
	 * TODO obv.
	 *
	 * @param string $name
	 *
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 */
	public function loggedin()
	{

		if($this->auth->acl_get('u_zone_view_login'))
		{
			$logged_in = $this->zone_players->get_logged_in();
			foreach($logged_in as $player)
			{
				$this->template->assign_block_vars('logged_in', array(
					'USERNAME' => $player['username'],
					'RATING' => $player['rating']
				));
			}

			$current_player = $this->zone_players->get_player($this->user->data['user_id']);

			if($this->auth->acl_get('u_zone_login'))
			{
				$this->template->assign_var('CURRENT_PLAYER_LOGGED_IN', $current_player['logged_in']);
				$this->template->assign_var('CURRENT_PLAYER_CAN_LOGIN', true);
			}

			// $this->template->assign_var('U_NCZONE_LOGIN', $this->helper->route('', array('action' => 'login')));
			// $this->template->assign_var('U_NCZONE_LOGOUT', $this->helper->route('', array('action' => 'logout')));
		}
	}

	public function rmatches()
	{
		return $this->helper->render('nczone_rmatches.html', $name);
	}

	public function pmatches()
	{
		return $this->helper->render('nczone_pmatches.html', $name);
	}

	public function table()
	{
		return $this->helper->render('nczone_table.html', $name);
	}
}
