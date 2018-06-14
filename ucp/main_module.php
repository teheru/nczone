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
use eru\nczone\utility\phpbb_util;

/**
 * nC Zone UCP module.
 */
class main_module
{
    var $u_action;

    function main($id, $mode)
    {
        $language = phpbb_util::language();
        $this->tpl_name = 'ucp_zone_body';
        $this->page_title = $language->lang('UCP_ZONE_TITLE');
        add_form_key('eru/nczone');

        if (phpbb_util::request()->is_set_post('submit')) {
            if (!check_form_key('eru/nczone')) {
                trigger_error($language->lang('FORM_INVALID'));
            }
        }
    }
}
