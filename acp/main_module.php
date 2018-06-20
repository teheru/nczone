<?php
/**
 *
 * nC Zone. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Marian Cepok, https://new-chapter.eu
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eru\nczone\acp;

use eru\nczone\utility\phpbb_util;

/**
 * nC Zone ACP module.
 */
class main_module
{
    public $page_title;
    public $tpl_name;
    public $u_action;

    public function main($id, $mode)
    {
        $config = phpbb_util::config();
        $request = phpbb_util::request();
        $language = phpbb_util::language();
        $language->add_lang('common', 'eru/nczone');
        $language->add_lang('info_acp', 'eru/nczone');
        $this->tpl_name = 'acp_nczone';
        $this->page_title = $language->lang('ACP_NCZONE_TITLE');
        add_form_key('eru/nczone');

        if ($request->is_set_post('submit')) {
            if (!check_form_key('eru/nczone')) {
                trigger_error('FORM_INVALID', E_USER_WARNING);
            }

            $config->set('nczone_draw_player_civs', (int)$request->variable('nczone_draw_player_civs', 600));
            $config->set('nczone_draw_team_civs', (int)$request->variable('nczone_draw_team_civs', 120));
            $config->set('nczone_draw_match_extra_civs', (int)$request->variable('nczone_draw_match_extra_civs', 4));
            $config->set('nczone_draw_team_extra_civs', (int)$request->variable('nczone_draw_team_extra_civs', 2));
            $player_num_civs = (int)$request->variable('nczone_draw_player_num_civs', 3);
            if($player_num_civs >= 1)
            {
                $config->set('nczone_draw_player_num_civs', $player_num_civs);
            } // todo: else

            trigger_error($language->lang('ACP_NCZONE_SAVED') . adm_back_link($this->u_action));
        }

        phpbb_util::template()->assign_vars(array(
            'U_ACTION' => $this->u_action,
            'nczone_draw_player_civs' => $config['nczone_draw_player_civs'],
            'nczone_draw_team_civs' => $config['nczone_draw_team_civs'],
            'nczone_draw_match_extra_civs' => $config['nczone_draw_match_extra_civs'],
            'nczone_draw_team_extra_civs' => $config['nczone_draw_team_extra_civs'],
            'nczone_draw_player_num_civs' => $config['nczone_draw_player_num_civs'],
        ));
    }
}
