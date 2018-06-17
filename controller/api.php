<?php

namespace eru\nczone\controller;

use eru\nczone\utility\phpbb_util;
use eru\nczone\utility\zone_util;
use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\user;
use Symfony\Component\HttpFoundation\JsonResponse;

class api
{
    /** @var config */
    protected $config;
    /** @var user */
    protected $user;
    /** @var auth */
    protected $auth;

    public function __construct(config $config, user $user, auth $auth)
    {
        $this->config = $config;
        $this->user = $user;
        $this->auth = $auth;
    }

    private function jsonResponse(array $data, int $code = 200)
    {
        return new JsonResponse($data, $code, [
            'Access-Control-Allow-Origin' => phpbb_util::request()->header('Origin') ?: '*',
            'Access-Control-Allow-Credentials' => 'true',
        ]);
    }

    /**
     * Returns infos about the current user
     *
     * @route /nczone/api/me
     *
     * @return JsonResponse
     */
    public function me(): JsonResponse
    {
        if (!$this->auth->acl_get('u_zone_view_login')) {
            return $this->jsonResponse([
                'id' => 0,
                'canViewLogin' => false,
                'canDraw' => false,
                'canLogin' => false,
            ]);
        }

        return $this->jsonResponse([
            'id' => (int)$this->user->data['user_id'],
            'canViewLogin' => true,
            'canDraw' => (bool)$this->auth->acl_get('u_zone_draw'),
            'canLogin' => (bool)$this->auth->acl_get('u_zone_login'),
        ]);
    }

    /**
     * Log me out of zone
     *
     * @route /nczone/api/me/logout
     *
     * @return JsonResponse
     */
    public function me_logout(): JsonResponse
    {
        # check is on login. used for logout as well.
        if (!(bool)$this->auth->acl_get('u_zone_login')) {
            return $this->jsonResponse(['reason' => 'Not Allowed to login.'], 403); // forbidden
        }

        $user_id = (int)$this->user->data['user_id'];
        $player = zone_util::players()->get_player($user_id);
        if (!isset($player['rating'])) {
            return $this->jsonResponse(['reason' => 'Not a player.'], 403); // forbidden
        }

        zone_util::players()->logout_player($user_id);
        return $this->jsonResponse([]);
    }

    /**
     * Log me in to zone
     *
     * @route /nczone/api/me/login
     *
     * @return JsonResponse
     */
    public function me_login(): JsonResponse
    {
        if (!(bool)$this->auth->acl_get('u_zone_login')) {
            return $this->jsonResponse(['reason' => 'Not Allowed to login.'], 403); // forbidden
        }

        $user_id = (int)$this->user->data['user_id'];
        $player = zone_util::players()->get_player($user_id);
        if (!isset($player['rating'])) {
            return $this->jsonResponse(['reason' => 'Not a player.'], 403); // forbidden
        }

        zone_util::players()->login_player($user_id);
        return $this->jsonResponse([]);
    }

    /**
     * Returns the list of logged in users as json.
     *
     * @route /nczone/api/users/logged_in
     *
     * @return JsonResponse
     */
    public function logged_in_users(): JsonResponse
    {
        $logged_in_users = zone_util::players()->get_logged_in();
        return $this->jsonResponse($logged_in_users);
    }

    /**
     * Returns the list of all users that can play in the zone.
     *
     * @route /nczone/api/users
     *
     * @return JsonResponse
     */
    public function all_users(): JsonResponse
    {
        $logged_in_users = zone_util::players()->get_all();
        return $this->jsonResponse($logged_in_users);
    }

    /**
     * Returns the list of running matches
     *
     * @route /nczone/api/rmatches
     *
     * @return JsonResponse
     */
    public function rmatches(): JsonResponse
    {
        // dummy matches... todo: replace with real matches lul
        $aztecs = (object)['id' => 3, 'title' => 'Aztecs'];
        $celts = (object)['id' => 9, 'title' => 'Celts'];
        $rmatches = [
            (object)[
                'id' => 12314,
                'game_type' => 'IP',
                'ip' => '',
                'timestampStart' => time() - 3600 - 600,
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
        return $this->jsonResponse($rmatches);
    }
}
