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

use eru\nczone\config\acl;
use eru\nczone\utility\db;
use eru\nczone\utility\phpbb_util;
use eru\nczone\utility\zone_util;

/**
 * nC Zone MCP module.
 */
class main_module
{
    public $u_action;
    public $tpl_name;
    public $page_title;

    function main($id, $mode)
    {
        $language = phpbb_util::language();
        $language->add_lang('info_mcp', 'eru/nczone');
        $this->page_title = $language->lang('MCP_ZONE_TITLE');
        add_form_key('eru/nczone');

        if (phpbb_util::request()->is_set_post('submit')) {
            if (!check_form_key('eru/nczone')) {
                trigger_error('FORM_INVALID', E_USER_WARNING);
            }
        }

        switch ($mode) {
            case 'players':
                $this->players();
                $this->tpl_name = 'mcp/players';
                break;

            case 'civs':
                $this->civs();
                $this->tpl_name = 'mcp/civs';
                break;

            case 'maps':
                $this->maps();
                $this->tpl_name = 'mcp/maps';
                break;

            case 'matches':
                $this->matches();
                $this->tpl_name = 'mcp/matches';
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
            // why is there no dependency injection for mcp?
            $db = new db($GLOBALS['db'], $GLOBALS['table_prefix']);
            $username = $request->variable('username', '');
            $user_id = (int)$db->get_var([
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
            if ($player->is_activated()) {
                $template->assign_var('S_EDIT_PLAYER', true);

                $template->assign_var('USERNAME', $player->get_username());
                $template->assign_var('PLAYER_RATING', $player->get_rating());
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
                    'ID' => $civ->get_id(),
                    'NAME' => $civ->get_name()
                ]);
            }
        } elseif ($civ_id == 0) // new civ
        {
            $template->assign_var('S_NEW_CIV', true);
        } else // civ selected
        {
            $template->assign_var('S_EDIT_CIV', true);

            $civ = zone_util::civs()->get_civ((int)$civ_id);
            $template->assign_var('S_CIV_ID', $civ->get_id());
            $template->assign_var('S_CIV_NAME', $civ->get_name());
        }
    }

    function maps()
    {
        $request = phpbb_util::request();

        $map_id = $request->variable('map_id', '');

        if ($request->variable('create_map', '') && phpbb_util::auth()->acl_get(acl::m_zone_create_maps)) {
            zone_util::maps()->create_map(
                $request->variable('map_name', ''),
                (float)$request->variable('map_weight', 1.0),
                (int)$request->variable('copy_map_id', 0)
            );
        } elseif ($request->variable('edit_map', '')) {
            zone_util::maps()->edit_map((int)$map_id, [
                'name' => $request->variable('map_name', ''),
                'weight' => (float)$request->variable('map_weight', 1.0),
            ]);

            $civ_info = [];
            foreach (zone_util::civs()->get_civs() as $civ) {
                $civId = $civ->get_id();
                $civ_info[$civId] = [
                    'multiplier' => $request->variable('multiplier_' . $civId, ''),
                    'force_draw' => $request->variable('force_draw_' . $civId, '') === 'on',
                    'prevent_draw' => $request->variable('prevent_draw_' . $civId, '') === 'on',
                    'both_teams' => $request->variable('both_teams_' . $civId, '') === 'on'
                ];
            }
            zone_util::maps()->edit_map_civs((int)$map_id, $civ_info);

            $map_id = '';
        }

        $template = phpbb_util::template();
        $template->assign_var('S_MANAGE_MAPS', true);
        $template->assign_var('S_CAN_CREATE_MAP', phpbb_util::auth()->acl_get(acl::m_zone_create_maps));

        if ($map_id == '') {
            $template->assign_var('S_SELECT_MAP', true);

            foreach (zone_util::maps()->get_maps() as $map) {
                $template->assign_block_vars('maps', [
                    'ID' => $map->get_id(),
                    'NAME' => $map->get_name(),
                ]);
            }
        } elseif ($map_id == 0) {
            $template->assign_var('S_NEW_MAP', true);

            foreach (zone_util::maps()->get_maps() as $map) {
                $template->assign_block_vars('maps', [
                    'ID' => $map->get_id(),
                    'NAME' => $map->get_name(),
                ]);
            }
        } else {
            $template->assign_var('S_EDIT_MAP', true);

            $map = zone_util::maps()->get_map($map_id);
            $template->assign_var('S_MAP_ID', $map->get_id());
            $template->assign_var('S_MAP_NAME', $map->get_name());
            $template->assign_var('S_MAP_WEIGHT', $map->get_weight());

            foreach (zone_util::maps()->get_map_civs($map->get_id()) as $map_civ) {
                # todo: dont fetch civs one by one in a loop.
                $civ = zone_util::civs()->get_civ((int)$map_civ['civ_id']);

                $template->assign_block_vars('map_civs', [
                    'CIV_ID' => $map_civ['civ_id'],
                    'CIV_NAME' => $civ->get_name(),
                    'MULTIPLIER' => $map_civ['multiplier'],
                    'FORCE_DRAW' => $map_civ['force_draw'] ? 'checked' : '',
                    'PREVENT_DRAW' => $map_civ['prevent_draw'] ? 'checked' : '',
                    'BOTH_TEAMS' => $map_civ['both_teams'] ? 'checked' : ''
                ]);
            }
        }
    }

    function matches()
    {
        $request = phpbb_util::request();
        $template = phpbb_util::template();

        $match_id = (int)$request->variable('match_id', '0');

        if($match_id) {
            if(zone_util::matches()->is_over($match_id)) {
                $old_match_winner = zone_util::matches()->get_winner($match_id);
                $new_match_winner = (int)$request->variable('match_winner', '-1');
                if($new_match_winner >= 0 && $new_match_winner <= 2) {
                    if($new_match_winner != $old_match_winner) {
                        zone_util::matches()->post_undo($match_id);
                        zone_util::matches()->post($match_id, 0, $new_match_winner, true); // todo: find out who I am
                        trigger_error('MCP_MATCH_REPOSTED');
                    }
                } else {
                    $template->assign_var('S_MATCH_ID', $match_id);
                    $template->assign_var('S_MATCH_WINNER', $old_match_winner);
                }
            } else {
                trigger_error('MCP_NO_POSTED_MATCH', E_USER_WARNING);
            }
        } else {
            $template->assign_var('S_SELECT_MATCH', true);
        }
    }
}
