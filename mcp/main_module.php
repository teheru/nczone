<?php
/**
 *
 * nC Zone. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Marian Cepok, https://new-chapter.eu
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eru\nczone\mcp;

use eru\nczone\utility\phpbb_util;
use eru\nczone\utility\zone_util;

/**
 * nC Zone MCP module.
 */
class main_module
{
    var $u_action;

    function main($id, $mode)
    {
        $user = phpbb_util::user();
        $user->add_lang_ext('eru/nczone', 'info_mcp');
        $this->tpl_name = 'mcp_zone_body';
        $this->page_title = $user->lang('MCP_ZONE_TITLE');
        add_form_key('eru/nczone');

        if (phpbb_util::request()->is_set_post('submit')) {
            if (!check_form_key('eru/nczone')) {
                trigger_error('FORM_INVALID', E_USER_WARNING);
            }
        }

        switch ($mode) {
            case 'players':
                $this->players();
                break;

            case 'civs':
                $this->civs();
                break;

            case 'maps':
                $this->maps();
                break;
        }

        phpbb_util::template()->assign_var('U_POST_ACTION', $this->u_action);
    }

    function players()
    {
        $template = phpbb_util::template();
        $request = phpbb_util::request();
        $db = phpbb_util::db();

        $template->assign_var('S_MANAGE_PLAYERS', true);

        $user_id = $request->variable('user_id', '');
        if ($user_id == '') {
            $username = $request->variable('username', '');
            $sql = 'SELECT user_id FROM ' . USERS_TABLE . " WHERE username_clean = '" . $db->sql_escape(utf8_clean_string($username)) . "'";
            $result = $db->sql_query($sql);
            $user_id = (int)$db->sql_fetchfield('user_id');
            $db->sql_freeresult($result);
        }

        $new_player = $request->variable('new_player', '0') == '1';
        $edit_player = $request->variable('edit_player', '0') == '1';
        if ($new_player) {
            $activate = $request->variable('activate', '') === 'on';
            if ($activate) {
                $rating = (int)$request->variable('rating', 0);
                zone_util::players()->activate_player((int)$user_id, $rating);
            }
        } elseif ($edit_player) {
            $rating = (int)$request->variable('rating', 0);
            zone_util::players()->edit_player($user_id, array('rating' => $rating));
        } else {
            if ($user_id) {
                $template->assign_var('USER_ID', $user_id);

                $player = zone_util::players()->get_player((int)$user_id);
                if (array_key_exists('rating', $player)) {
                    $template->assign_var('S_EDIT_PLAYER', true);

                    $template->assign_var('USERNAME', $player['username']);
                    $template->assign_var('PLAYER_RATING', $player['rating']);
                } else {
                    $template->assign_var('S_NEW_PLAYER', true);
                }
            }
        }

        if ($username != '') {
            $template->assign_var('S_PLAYER_NOT_FOUND', true);
        }
        $template->assign_var('S_SELECT_PLAYER', true);
        $template->assign_var('U_FIND_USERNAME', append_sid(phpbb_util::file_url('memberlist'), 'mode=searchuser&amp;form=mcp&amp;field=username&amp;select_single=true'));
    }

    function civs()
    {
        $template = phpbb_util::template();
        $request = phpbb_util::request();

        $template->assign_var('S_MANAGE_CIVS', true);

        $civ_id = $request->variable('civ_id', '');

        $create_civ = $request->variable('create_civ', '');
        $edit_civ = $request->variable('edit_civ', '');
        if ($create_civ) {
            $civ_name = $request->variable('civ_name', '');
            zone_util::civs()->create_civ($civ_name);
        } elseif ($edit_civ) {
            $civ_name = $request->variable('civ_name', '');
            zone_util::civs()->edit_civ((int)$civ_id, array('name' => $civ_name));
            $civ_id = ''; // back to selection page
        }

        if ($civ_id == '') // no civ selected
        {
            $template->assign_var('S_SELECT_CIV', true);

            $civs = zone_util::civs()->get_civs();
            if (count($civs)) {
                foreach ($civs as $civ) {
                    $template->assign_block_vars('civs', array(
                        'ID' => (int)$civ['id'],
                        'NAME' => $civ['name']
                    ));
                }
            }
        } elseif ($civ_id == 0) // new civ
        {
            $template->assign_var('S_NEW_CIV', true);
        } else // civ selected
        {
            $template->assign_var('S_EDIT_CIV', true);

            $civ = zone_util::civs()->get_civ((int)$civ_id);
            $template->assign_var('S_CIV_ID', $civ_id);
            $template->assign_var('S_CIV_NAME', $civ['name']);
        }
    }

    function maps()
    {
        $template = phpbb_util::template();
        $request = phpbb_util::request();

        $map_id = $request->variable('map_id', '');

        $create_map = $request->variable('create_map', '');
        $edit_map = $request->variable('edit_map', '');
        if ($create_map && phpbb_util::auth()->acl_get('m_zone_create_maps')) {
            $map_name = $request->variable('map_name', '');
            $map_weight = (float)$request->variable('map_weight', 0.0);
            $copy_map_id = (int)$request->variable('copy_map_id', 0);
            zone_util::maps()->create_map($map_name, $map_weight, $copy_map_id);
        } elseif ($edit_map) {
            $map_name = $request->variable('map_name', '');
            $map_weight = (float)$request->variable('map_weight', 0.0);
            zone_util::maps()->edit_map((int)$map_id, array(
                    'name' => $map_name,
                    'weight' => $map_weight)
            );

            $civs = zone_util::civs()->get_civs();
            $civ_info = array();
            foreach ($civs as $civ) {
                $civ_id = $civ['id'];

                $multiplier = $request->variable('multiplier_' . $civ_id, '');
                $force_draw = $request->variable('force_draw_' . $civ_id, '');
                $prevent_draw = $request->variable('prevent_draw_' . $civ_id, '');
                $both_teams = $request->variable('both_teams_' . $civ_id, '');

                $civ_info[$civ_id] = array(
                    'multiplier' => $multiplier,
                    'force_draw' => ($force_draw === 'on'),
                    'prevent_draw' => ($prevent_draw === 'on'),
                    'both_teams' => ($both_teams === 'on')
                );
            }
            zone_util::maps()->edit_map_civs((int)$map_id, $civ_info);

            $map_id = '';
        }

        $template->assign_var('S_MANAGE_MAPS', true);
        $template->assign_var('S_CAN_CREATE_MAP', phpbb_util::auth()->acl_get('m_zone_create_maps'));

        if ($map_id == '') {
            $template->assign_var('S_SELECT_MAP', true);

            $maps = zone_util::maps()->get_maps();
            if (count($maps)) {
                foreach ($maps as $map) {
                    $template->assign_block_vars('maps', array(
                        'ID' => (int)$map['id'],
                        'NAME' => $map['name']
                    ));
                }
            }
        } elseif ($map_id == 0) {
            $template->assign_var('S_NEW_MAP', true);

            $maps = zone_util::maps()->get_maps();
            if (count($maps)) {
                foreach ($maps as $map) {
                    $template->assign_block_vars('maps', array(
                        'ID' => (int)$map['id'],
                        'NAME' => $map['name']
                    ));
                }
            }
        } else {
            $template->assign_var('S_EDIT_MAP', true);

            $map = zone_util::maps()->get_map($map_id);
            $template->assign_var('S_MAP_ID', $map_id);
            $template->assign_var('S_MAP_NAME', $map['name']);
            $template->assign_var('S_MAP_WEIGHT', $map['weight']);

            $map_civs = zone_util::maps()->get_map_civs($map_id);
            foreach ($map_civs as $map_civ) {
                $civ = zone_util::civs()->get_civ((int)$map_civ['civ_id']);

                $template->assign_block_vars('map_civs', array(
                    'CIV_ID' => $map_civ['civ_id'],
                    'CIV_NAME' => $civ['name'],
                    'MULTIPLIER' => $map_civ['multiplier'],
                    'FORCE_DRAW' => $map_civ['force_draw'] ? 'checked' : '',
                    'PREVENT_DRAW' => $map_civ['prevent_draw'] ? 'checked' : '',
                    'BOTH_TEAMS' => $map_civ['both_teams'] ? 'checked' : ''
                ));
            }
        }
    }
}
