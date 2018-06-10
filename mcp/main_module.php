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

use eru\nczone\utility\db_util;
use eru\nczone\utility\phpbb_util;
use eru\nczone\utility\zone_util;

/**
 * nC Zone MCP module.
 */
class main_module
{
    private $u_action;
    private $tpl_name;
    private $page_title;

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

        $template->assign_var('S_MANAGE_PLAYERS', true);

        $user_id = $request->variable('user_id', '');
        if ($user_id == '') {
            $db = phpbb_util::db();
            $username = $request->variable('username', '');
            $user_id = (int)db_util::get_var($db, [
                'SELECT' => 'user_id',
                'FROM' => [USERS_TABLE => 'u'],
                'WHERE' => 'username_clean = \'' . $db->sql_escape(utf8_clean_string($username)) . '\'',
            ]);
        } else {
            $username = '';
        }

        if ($request->variable('new_player', '0') == '1') {
            $activate = $request->variable('activate', '') === 'on';
            if ($activate) {
                zone_util::players()->activate_player((int)$user_id, (int)$request->variable('rating', 0));
            }
        } elseif ($request->variable('edit_player', '0') == '1') {
            zone_util::players()->edit_player((int)$user_id, ['rating' => (int)$request->variable('rating', 0)]);
        } elseif ($user_id) {
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

        if ($username !== '') {
            $template->assign_var('S_PLAYER_NOT_FOUND', true);
        }
        $template->assign_var('S_SELECT_PLAYER', true);
        $template->assign_var('U_FIND_USERNAME', append_sid(phpbb_util::file_url('memberlist'), 'mode=searchuser&amp;form=mcp&amp;field=username&amp;select_single=true'));
    }

    function civs()
    {
        $request = phpbb_util::request();

        $civ_id = $request->variable('civ_id', '');

        if ($request->variable('create_civ', '')) {
            zone_util::civs()->create_civ($request->variable('civ_name', ''));
        } elseif ($request->variable('edit_civ', '')) {
            zone_util::civs()->edit_civ((int)$civ_id, [
                'name' => $request->variable('civ_name', ''),
            ]);
            $civ_id = ''; // back to selection page
        }

        $template = phpbb_util::template();
        $template->assign_var('S_MANAGE_CIVS', true);

        if ($civ_id == '') // no civ selected
        {
            $template->assign_var('S_SELECT_CIV', true);
            foreach (zone_util::civs()->get_civs() as $civ) {
                $template->assign_block_vars('civs', [
                    'ID' => (int)$civ['id'],
                    'NAME' => $civ['name']
                ]);
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
        $request = phpbb_util::request();

        $map_id = $request->variable('map_id', '');

        if ($request->variable('create_map', '') && phpbb_util::auth()->acl_get('m_zone_create_maps')) {
            zone_util::maps()->create_map(
                $request->variable('map_name', ''),
                (float)$request->variable('map_weight', 0.0),
                (int)$request->variable('copy_map_id', 0)
            );
        } elseif ($request->variable('edit_map', '')) {
            zone_util::maps()->edit_map((int)$map_id, [
                'name' => $request->variable('map_name', ''),
                'weight' => (float)$request->variable('map_weight', 0.0),
            ]);

            $civ_info = [];
            foreach (zone_util::civs()->get_civs() as $civ) {
                $civ_info[$civ['id']] = [
                    'multiplier' => $request->variable('multiplier_' . $civ['id'], ''),
                    'force_draw' => $request->variable('force_draw_' . $civ['id'], '') === 'on',
                    'prevent_draw' => $request->variable('prevent_draw_' . $civ['id'], '') === 'on',
                    'both_teams' => $request->variable('both_teams_' . $civ['id'], '') === 'on'
                ];
            }
            zone_util::maps()->edit_map_civs((int)$map_id, $civ_info);

            $map_id = '';
        }

        $template = phpbb_util::template();
        $template->assign_var('S_MANAGE_MAPS', true);
        $template->assign_var('S_CAN_CREATE_MAP', phpbb_util::auth()->acl_get('m_zone_create_maps'));

        if ($map_id == '') {
            $template->assign_var('S_SELECT_MAP', true);

            foreach (zone_util::maps()->get_maps() as $map) {
                $template->assign_block_vars('maps', [
                    'ID' => (int)$map['id'],
                    'NAME' => $map['name']
                ]);
            }
        } elseif ($map_id == 0) {
            $template->assign_var('S_NEW_MAP', true);

            foreach (zone_util::maps()->get_maps() as $map) {
                $template->assign_block_vars('maps', [
                    'ID' => (int)$map['id'],
                    'NAME' => $map['name']
                ]);
            }
        } else {
            $template->assign_var('S_EDIT_MAP', true);

            $map = zone_util::maps()->get_map($map_id);
            $template->assign_var('S_MAP_ID', $map_id);
            $template->assign_var('S_MAP_NAME', $map['name']);
            $template->assign_var('S_MAP_WEIGHT', $map['weight']);

            foreach (zone_util::maps()->get_map_civs($map_id) as $map_civ) {
                # todo: dont fetch civs one by one in a loop.
                $civ = zone_util::civs()->get_civ((int)$map_civ['civ_id']);

                $template->assign_block_vars('map_civs', [
                    'CIV_ID' => $map_civ['civ_id'],
                    'CIV_NAME' => $civ['name'],
                    'MULTIPLIER' => $map_civ['multiplier'],
                    'FORCE_DRAW' => $map_civ['force_draw'] ? 'checked' : '',
                    'PREVENT_DRAW' => $map_civ['prevent_draw'] ? 'checked' : '',
                    'BOTH_TEAMS' => $map_civ['both_teams'] ? 'checked' : ''
                ]);
            }
        }
    }
}
