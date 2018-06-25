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
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * nC Zone Event listener.
 */
class main_listener implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'				=> 'load_language_on_setup',
			'core.page_header'				=> 'add_page_header_link',
			'core.viewonline_overwrite_location'	=> 'viewonline_page',
			'core.permissions'				=> 'load_permission_language',
		);
	}

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\user */
	protected $user;

	/** @var string phpEx */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\controller\helper	$helper		Controller helper object
	 * @param \phpbb\template\template	$template	Template object
	 * @param \phpbb\user               $user       User object
	 * @param string                    $php_ext    phpEx
	 */
	public function __construct(\phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user, $php_ext)
	{
		$this->helper   = $helper;
		$this->template = $template;
		$this->user     = $user;
		$this->php_ext  = $php_ext;
	}


  public function load_permission_language(\phpbb\event\data $event)
  {
	$permissions = $event['permissions'];

    $permissions['u_zone_view_info']	= array('lang' => 'ACL_U_ZONE_VIEW_INFO', 'cat' => 'zone');
    $permissions['u_zone_login']	= array('lang' => 'ACL_U_ZONE_LOGIN', 'cat' => 'zone');
    $permissions['u_zone_view_login']	= array('lang' => 'ACL_U_ZONE_VIEW_LOGIN', 'cat' => 'zone');
    $permissions['u_zone_draw']	= array('lang' => 'ACL_U_ZONE_DRAW', 'cat' => 'zone');
    $permissions['u_zone_view_matches']	= array('lang' => 'ACL_U_ZONE_VIEW_MATCHES', 'cat' => 'zone');
    $permissions['u_zone_view_bets']	= array('lang' => 'ACL_U_ZONE_VIEW_BETS', 'cat' => 'zone');
    $permissions['u_zone_bet']	= array('lang' => 'ACL_U_ZONE_BET', 'cat' => 'zone');

    $permissions['m_zone_manage_players']	= array('lang' => 'ACL_M_ZONE_MANAGE_PLAYERS', 'cat' => 'zone');
    $permissions['m_zone_manage_civs']	= array('lang' => 'ACL_M_ZONE_MANAGE_CIVS', 'cat' => 'zone');
    $permissions['m_zone_manage_maps']	= array('lang' => 'ACL_M_ZONE_MANAGE_MAPS', 'cat' => 'zone');
	$permissions['m_zone_create_maps']	= array('lang' => 'ACL_M_ZONE_CREATE_MAPS', 'cat' => 'zone');
    $permissions['m_zone_login_players']	= array('lang' => 'ACL_M_ZONE_LOGIN_PLAYERS', 'cat' => 'zone');
	$permissions['m_zone_draw_match']	= array('lang' => 'ACL_M_ZONE_DRAW_MATCH', 'cat' => 'zone');
	$permissions['m_zone_change_match']	= array('lang' => 'ACL_M_ZONE_CHANGE_MATCH', 'cat' => 'zone');

	$permissions['a_zone_manage_general']	= array('lang' => 'ACL_A_ZONE_MANAGE_GENERAL', 'cat' => 'zone');
	$permissions['a_zone_manage_draw']	= array('lang' => 'ACL_A_ZONE_MANAGE_DRAW', 'cat' => 'zone');


	$event['categories'] = array_merge($event['categories'], array(
		'zone' => 'ACP_CAT_ZONE',
	));

	$event['permissions'] = $permissions;
  }

	/**
	 * Load common language files during user setup
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'eru/nczone',
			'lang_set' => 'common',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	 * Add a link to the controller in the forum navbar
	 */
	public function add_page_header_link()
	{
		$this->template->assign_vars(array(
			'U_NCZONE'	=> $this->helper->route('eru_nczone_controller_zone'),
		));
	}

	/**
	 * Show users viewing Acme Demo on the Who Is Online page
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function viewonline_page($event)
	{
		if ($event['on_page'][1] === 'app' && strrpos($event['row']['session_page'], 'app.' . $this->php_ext . '/nczone') === 0)
		{
			$event['location'] = $this->user->lang('NCZONE_RMATCHES');
			$event['location_url'] = $this->helper->route('eru_nczone_controller_zone', array('name' => 'world'));
		}
	}
}
