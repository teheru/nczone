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

use eru\nczone\config\config;
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
        $request = phpbb_util::request();
        $language = phpbb_util::language();
        $language->add_lang('common', 'eru/nczone');
        $language->add_lang('info_acp', 'eru/nczone');
        $this->page_title = $language->lang('ACP_NCZONE_TITLE');
        add_form_key('eru/nczone');

        $submit = False;
        if ($request->is_set_post('submit')) {
            if (!check_form_key('eru/nczone')) {
                trigger_error('FORM_INVALID', E_USER_WARNING);
            }

            $submit = True;
        }

        switch($mode)
        {
            case 'general':
                $this->general($submit);
                $this->tpl_name = 'acp_nczone_general';
                break;
            case 'draw':
                $this->draw($submit);
                $this->tpl_name = 'acp_nczone_draw';
                break;
        }
    }

    public function draw(bool $submit): void
    {
        $request = phpbb_util::request();
        $language = phpbb_util::language();

        if($submit)
        {
            config::set(config::draw_time, (int)$request->variable('nczone_draw_time', config::default(config::draw_time)));
            config::set(config::draw_player_civs, (int)$request->variable('nczone_draw_player_civs', config::default(config::draw_player_civs)));
            config::set(config::draw_team_civs, (int)$request->variable('nczone_draw_team_civs', config::default(config::draw_team_civs)));
            config::set(config::draw_match_extra_civs_1vs1, (int)$request->variable('nczone_draw_match_extra_civs_1vs1', config::default(config::draw_match_extra_civs_1vs1)));
            config::set(config::draw_match_extra_civs_2vs2, (int)$request->variable('nczone_draw_match_extra_civs_2vs2', config::default(config::draw_match_extra_civs_2vs2)));
            config::set(config::draw_match_extra_civs_3vs3, (int)$request->variable('nczone_draw_match_extra_civs_3vs3', config::default(config::draw_match_extra_civs_3vs3)));
            config::set(config::draw_match_extra_civs_4vs4, (int)$request->variable('nczone_draw_match_extra_civs_4vs4', config::default(config::draw_match_extra_civs_4vs4)));
            config::set(config::draw_team_extra_civs_1vs1, (int)$request->variable('nczone_draw_team_extra_civs_1vs1', config::default(config::draw_team_extra_civs_1vs1)));
            config::set(config::draw_team_extra_civs_2vs2, (int)$request->variable('nczone_draw_team_extra_civs_2vs2', config::default(config::draw_team_extra_civs_2vs2)));
            config::set(config::draw_team_extra_civs_3vs3, (int)$request->variable('nczone_draw_team_extra_civs_3vs3', config::default(config::draw_team_extra_civs_3vs3)));
            config::set(config::draw_team_extra_civs_4vs4, (int)$request->variable('nczone_draw_team_extra_civs_4vs4', config::default(config::draw_team_extra_civs_4vs4)));
            config::set(config::draw_factor, $request->variable('nczone_draw_factor', (float)config::default(config::draw_factor)));

            $draw_block_time = (int)config::default(config::draw_block_time);
            if ($draw_block_time >= 0 && $draw_block_time <= 1440) {
                config::set(config::draw_block_time, $request->variable('nczone_draw_block_time', $draw_block_time));
            }
            $draw_block_after_match = (int)config::default(config::draw_block_after_match);
            if ($draw_block_after_match >= 0 && $draw_block_after_match <= 10) {
                config::set(config::draw_block_after_match, $request->variable('nczone_draw_block_after_match', $draw_block_after_match));
            }
            $player_num_civs_1vs1 = (int)$request->variable('nczone_draw_player_num_civs_1vs1', config::default(config::draw_player_num_civs_1vs1));
            $player_num_civs_2vs2 = (int)$request->variable('nczone_draw_player_num_civs_2vs2', config::default(config::draw_player_num_civs_2vs2));
            $player_num_civs_3vs3 = (int)$request->variable('nczone_draw_player_num_civs_3vs3', config::default(config::draw_player_num_civs_3vs3));
            $player_num_civs_4vs4 = (int)$request->variable('nczone_draw_player_num_civs_4vs4', config::default(config::draw_player_num_civs_4vs4));
            if($player_num_civs_1vs1 >= 1 && $player_num_civs_2vs2 >= 1 && $player_num_civs_3vs3 >= 1 && $player_num_civs_4vs4 >= 1)
            {
                config::set(config::draw_player_num_civs_1vs1, $player_num_civs_1vs1);
                config::set(config::draw_player_num_civs_2vs2, $player_num_civs_2vs2);
                config::set(config::draw_player_num_civs_3vs3, $player_num_civs_3vs3);
                config::set(config::draw_player_num_civs_4vs4, $player_num_civs_4vs4);
            } // todo: else

            trigger_error($language->lang('ACP_NCZONE_SAVED') . adm_back_link($this->u_action));
        }

        phpbb_util::template()->assign_vars(array(
            'U_ACTION' => $this->u_action,
            'nczone_draw_time' => config::get(config::draw_time),
            'nczone_rules_post_id' => config::get(config::rules_post_id),
            'nczone_draw_player_civs' => config::get(config::draw_player_civs),
            'nczone_draw_team_civs' => config::get(config::draw_team_civs),
            'nczone_draw_match_extra_civs_1vs1' => config::get(config::draw_match_extra_civs_1vs1),
            'nczone_draw_match_extra_civs_2vs2' => config::get(config::draw_match_extra_civs_2vs2),
            'nczone_draw_match_extra_civs_3vs3' => config::get(config::draw_match_extra_civs_3vs3),
            'nczone_draw_match_extra_civs_4vs4' => config::get(config::draw_match_extra_civs_4vs4),
            'nczone_draw_team_extra_civs_1vs1' => config::get(config::draw_team_extra_civs_1vs1),
            'nczone_draw_team_extra_civs_2vs2' => config::get(config::draw_team_extra_civs_2vs2),
            'nczone_draw_team_extra_civs_3vs3' => config::get(config::draw_team_extra_civs_3vs3),
            'nczone_draw_team_extra_civs_4vs4' => config::get(config::draw_team_extra_civs_4vs4),
            'nczone_draw_player_num_civs_1vs1' => config::get(config::draw_player_num_civs_1vs1),
            'nczone_draw_player_num_civs_2vs2' => config::get(config::draw_player_num_civs_2vs2),
            'nczone_draw_player_num_civs_3vs3' => config::get(config::draw_player_num_civs_3vs3),
            'nczone_draw_player_num_civs_4vs4' => config::get(config::draw_player_num_civs_4vs4),
            'nczone_draw_factor' => config::get(config::draw_factor),
            'nczone_draw_block_time' => config::get(config::draw_block_time),
            'nczone_draw_block_after_match' => config::get(config::draw_block_after_match),
        ));
    }

    public function general(bool $submit): void
    {
        $request = phpbb_util::request();
        $language = phpbb_util::language();

        if($submit)
        {
            config::set(config::rules_post_id, (int)$request->variable('nczone_rules_post_id', config::default(config::rules_post_id)));
            config::set(config::match_forum_id, (int)$request->variable('nczone_match_forum_id', config::default(config::match_forum_id)));
            config::set(config::pmatches_page_size, (int)$request->variable('nczone_pmatches_page_size', config::default(config::pmatches_page_size)));
            config::set(config::activity_time, (int)$request->variable('nczone_activity_time', config::default(config::activity_time)));
            config::set(config::activity_1, (int)$request->variable('nczone_activity_1', config::default(config::activity_1)));
            config::set(config::activity_2, (int)$request->variable('nczone_activity_2', config::default(config::activity_2)));
            config::set(config::activity_3, (int)$request->variable('nczone_activity_3', config::default(config::activity_3)));
            config::set(config::activity_4, (int)$request->variable('nczone_activity_4', config::default(config::activity_4)));
            config::set(config::activity_5, (int)$request->variable('nczone_activity_5', config::default(config::activity_5)));
            config::set(config::bet_time, (int)$request->variable('nczone_bet_time', config::default(config::bet_time)));
            config::set(config::points_1vs1, (int)$request->variable('nczone_points_1vs1', config::default(config::points_1vs1)));
            config::set(config::points_2vs2, (int)$request->variable('nczone_points_2vs2', config::default(config::points_2vs2)));
            config::set(config::points_3vs3, (int)$request->variable('nczone_points_3vs3', config::default(config::points_3vs3)));
            config::set(config::points_4vs4, (int)$request->variable('nczone_points_4vs4', config::default(config::points_4vs4)));
            config::set(config::extra_points, (int)$request->variable('nczone_extra_points', config::default(config::extra_points)));
            config::set(config::info_posts, implode(',', array_map('\intval', preg_split('/$\R?^/m', $request->variable('nczone_info_posts', '')))));

            trigger_error($language->lang('ACP_NCZONE_SAVED') . adm_back_link($this->u_action));
        }

        phpbb_util::template()->assign_vars(array(
            'U_ACTION' => $this->u_action,
            'nczone_rules_post_id' => config::get(config::rules_post_id),
            'nczone_match_forum_id' => config::get(config::match_forum_id),
            'nczone_pmatches_page_size' => config::get(config::pmatches_page_size),
            'nczone_activity_time' => config::get(config::activity_time),
            'nczone_activity_1' => config::get(config::activity_1),
            'nczone_activity_2' => config::get(config::activity_2),
            'nczone_activity_3' => config::get(config::activity_3),
            'nczone_activity_4' => config::get(config::activity_4),
            'nczone_activity_5' => config::get(config::activity_5),
            'nczone_bet_time' => config::get(config::bet_time),
            'nczone_points_1vs1' => config::get(config::points_1vs1),
            'nczone_points_2vs2' => config::get(config::points_2vs2),
            'nczone_points_3vs3' => config::get(config::points_3vs3),
            'nczone_points_4vs4' => config::get(config::points_4vs4),
            'nczone_extra_points' => config::get(config::extra_points),
            'nczone_info_posts' => str_replace(',', "\n", config::get(config::info_posts)),
        ));
    }
}
