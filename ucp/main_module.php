<?php
/**
 *
 * nC Zone. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Marian Cepok, https://new-chapter.eu
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eru\nczone\ucp;

/**
 * nC Zone UCP module.
 */
class main_module
{
	var $u_action;

	function main($id, $mode)
	{
		global $db, $request, $template, $user;

		$this->tpl_name = 'ucp_zone_body';
		$this->page_title = $user->lang('UCP_ZONE_TITLE');
		add_form_key('eru/nczone');

		if ($request->is_set_post('submit'))
		{
			if (!check_form_key('eru/nczone'))
			{
				trigger_error($user->lang('FORM_INVALID'));
			}
	}
}
