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

use eru\nczone\utility\phpbb_util;

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

    /* @var \phpbb\request\request_interface */
    protected $request;

    /* @var \phpbb\user */
    protected $user;

    /* @var \eru\nczone\zone\players */
    protected $zone_players;

    /* @var \eru\nczone\zone\matches */
    protected $zone_matches;

    /**
     * Constructor
     *
     * @param \phpbb\config\config $config
     * @param \phpbb\controller\helper $helper
     * @param \phpbb\template\template $template
     * @param \phpbb\request\request_interface $request
     * @param \phpbb\user $user
     * @param \eru\nczone\zone\players $zone_players
     * @param \eru\nczone\zone\matches $zone_matches
     */
    public function __construct(\phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\request\request_interface $request, \phpbb\user $user, \phpbb\auth\auth $auth, \eru\nczone\zone\players $zone_players, \eru\nczone\zone\matches $zone_matches)
    {
        $this->config = $config;
        $this->helper = $helper;
        $this->template = $template;
        $this->request = $request;
        $this->user = $user;
        $this->auth = $auth;
        $this->zone_players = $zone_players;
        $this->zone_matches = $zone_matches;
    }

    public function common()
    {
        // links menu
        $this->template->assign_var('U_NCZONE_RMATCHES', $this->helper->route('eru_nczone_controller_rmatches'));
        $this->template->assign_var('U_NCZONE_PMATCHES', $this->helper->route('eru_nczone_controller_pmatches'));
        $this->template->assign_var('U_NCZONE_TABLE', $this->helper->route('eru_nczone_controller_table'));

        // logged in list
        if ($this->auth->acl_get('u_zone_view_login')) {
            $this->template->assign_var('CURRENT_PLAYER_VIEW_LOGIN', true);
            if ($this->auth->acl_get('u_zone_draw')) {
                $this->template->assign_var('CURRENT_PLAYER_CAN_DRAW', true);
            }

            $logged_in = $this->zone_players->get_logged_in();
            foreach ($logged_in as $player) {
                $this->template->assign_block_vars('logged_in', array(
                    'USERNAME' => $player['username'],
                    'RATING' => $player['rating']
                ));
            }

            $current_player = $this->zone_players->get_player((int)$this->user->data['user_id']);

            if ($this->auth->acl_get('u_zone_login')) {
                $this->template->assign_var('CURRENT_PLAYER_LOGGED_IN', $current_player['logged_in']);
                $this->template->assign_var('CURRENT_PLAYER_CAN_LOGIN', true);
            }

            // $this->template->assign_var('U_ZONE_LOGINOUT', '');
        }
    }

    public function rmatches()
    {
        $this->common();

        $language = phpbb_util::language();
        $language->add_lang('rmatches', 'eru/nczone');

        // dummy matches... todo: replace with real matches lul
        $aztecs = (object)['id' => 3, 'title' => 'Aztecs'];
        $celts = (object)['id' => 9, 'title' => 'Celts'];
        $rmatches = [
            (object)[
                'id' => 12314,
                'game_type' => 'IP',
                'ip' => '',
                'timestampStart' => 19199191,
                'timestampEnd' => 0,
                'winner' => 0, // 1 = TEAM 1, 2 = TEAM 2, 3 = OW, 4 = DRAW
                'result_poster' => null,
                'drawer' => (object)[
                    'id' => 1234,
                    'name' => 'Hubert'
                ],
                'map' => (object)[
                    'id' => 123,
                    'title' => 'arab'
                ],
                'civs' => (object)[
                    'both' => [
                        (object)['id' => 1, 'title' => 'Huns'],
                    ],
                    'team1' => [
                        $aztecs,
                        (object)['id' => 4, 'title' => 'Britons'],
                    ],
                    'team2' => [
                        (object)['id' => 6, 'title' => 'Burmese'],
                        $celts,
                    ]
                ],
                'bets' => (object)[
                    'team1' => [
                        (object)[
                            'timestamp' => 51515151,
                            'user' => [
                                'id' => 1234,
                                'name' => 'Hubert',
                            ]
                        ]
                    ],
                    'team2' => [
                        (object)[
                            'timestamp' => 51515151,
                            'user' => [
                                'id' => 4444,
                                'name' => 'Basti_der_Spasti'
                            ]
                        ],
                        (object)[
                            'timestamp' => 666666,
                            'user' => [
                                'id' => 1234,
                                'name' => 'Kacknouhb'
                            ]
                        ],
                    ]
                ],
                'players' => (object)[
                    'team1' => [
                        (object)[
                            'id' => 1234,
                            'name' => 'Kacknouhb',
                            'rating' => 1377,
                            'rating_change' => 0,
                        ],
                        (object)[
                            'id' => 2135,
                            'name' => 'Ferdinand der Hauser',
                            'rating' => 899,
                            'rating_change' => 0,
                        ],
                        (object)[
                            'id' => 4444,
                            'name' => 'Basti_der_Spasti',
                            'rating' => 61,
                            'rating_change' => 0,
                        ],
                        (object)[
                            'id' => 5,
                            'name' => 'Luemmel_87',
                            'rating' => 371,
                            'rating_change' => 0,
                            'civ' => $celts,
                        ],
                    ],
                    'team2' => [
                        (object)[
                            'id' => 1234,
                            'name' => 'Ultra-Schleife',
                            'rating' => 1500,
                            'rating_change' => 0,
                        ],
                        (object)[
                            'id' => 898,
                            'name' => 'Zebrator',
                            'rating' => 400,
                            'rating_change' => 0,
                        ],
                        (object)[
                            'id' => 3,
                            'name' => 'Mr. Snipe',
                            'rating' => 370,
                            'rating_change' => 0,
                        ],
                        (object)[
                            'id' => 4,
                            'name' => 'Frank Bank',
                            'rating' => 371,
                            'rating_change' => 0,
                            'civ' => $aztecs,
                        ],
                    ],
                ],
            ]
        ];
        phpbb_util::template()->assign_var('rmatches', $rmatches);
        return $this->helper->render('zone/rmatches.html');
    }

    public function pmatches()
    {
        $this->common();
        return $this->helper->render('zone/pmatches.html');
    }

    public function table()
    {
        $this->common();
        return $this->helper->render('zone/table.html');
    }
}
