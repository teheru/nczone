<?php

namespace eru\nczone\controller;

use eru\nczone\config\acl;
use eru\nczone\config\config;
use eru\nczone\config\user_settings;
use eru\nczone\utility\phpbb_util;
use eru\nczone\utility\zone_util;
use eru\nczone\zone\entity\map;
use eru\nczone\zone\error\BadRequestError;
use eru\nczone\zone\error\ForbiddenError;
use phpbb\request\request_interface;
use phpbb\user;
use Symfony\Component\HttpFoundation\JsonResponse;

class api
{
    /** @var user */
    protected $user;

    /** @var config */
    protected $config;

    /** @var acl */
    protected $acl;

    private const CODE_FORBIDDEN = 403;
    private const CODE_OK = 200;
    private const CODE_BAD_REQUEST = 400;
    private const CODE_INTERNAL_SERVER_ERROR = 500;

    public function __construct(user $user, config $config, acl $acl)
    {
        $this->user = $user;
        $this->config = $config;
        $this->acl = $acl;
    }

    private function respond(
        callable $callable,
        array $acl = [],
        array $args = []
    ): JsonResponse {
        $request = phpbb_util::request();
        $headers = [
            'Access-Control-Allow-Origin' => $request->header('Origin') ?: '*',
            'Access-Control-Allow-Credentials' => 'true',
        ];

        $server = $request->get_super_global(request_interface::SERVER);
        if (($server['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
            return new JsonResponse([], self::CODE_OK, \array_merge($headers, [
                'Access-Control-Allow-Headers' => 'x-update-session',
                'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS, PUT',
            ]));
        }

        if ($request->header('x-update-session') === '1') {
            $this->user->update_session_infos();
        }

        try {
            foreach ($acl as $perm => $err) {
                if (!$this->has_permission($perm)) {
                    throw new ForbiddenError($err);
                }
            }
            return new JsonResponse($callable($args), self::CODE_OK, $headers);
        } catch (ForbiddenError $t) {
            return new JsonResponse([
                'reason' => $t->getMessage(),
            ], self::CODE_FORBIDDEN, $headers);
        } catch (BadRequestError $t) {
            return new JsonResponse([
                'reason' => $t->getMessage(),
            ], self::CODE_BAD_REQUEST, $headers);
        } catch (\Throwable $t) {
            return new JsonResponse([
                'error' => \get_class($t),
                'message' => $t->getMessage(),
            ], self::CODE_INTERNAL_SERVER_ERROR, $headers);
        }
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
        return $this->respond(function () {
            $user_id = $this->get_user_id();
            return [
                'id' => $user_id,
                'title' => $this->config->get(config::title),
                'sid' => $this->user->session_id,
                'lang' => $this->user->data['user_lang'],
                'permissions' => $this->acl->all_user_permissions(
                    self::is_activated($user_id)
                ),
                'settings' => zone_util::players()->get_settings($user_id)
            ];
        });
    }

    public function me_get_settings(): JsonResponse
    {
        return $this->respond(function () {
            $user_id = $this->get_user_id();
            return zone_util::players()->get_settings($user_id);
        });
    }


    /**
     * Sets a user settings. Returns the new settings.
     *
     * @route /nczone/api/me/settings
     *
     * @return JsonResponse
     */
    public function me_set_settings(): JsonResponse
    {
        return $this->respond(function () {
            $data = self::get_request_data()['settings'];

            foreach ($data as $setting => $value) {
                if (!array_key_exists($setting, user_settings::SETTINGS)) {
                    return ['error' => 'SETTING_NOT_FOUND'];
                }
                if (!preg_match(user_settings::SETTINGS[$setting]['pattern'], $value)) {
                    return ['error' => 'VALUE_NOT_VALID'];
                }
            }

            $user_id = $this->get_user_id();
            foreach ($data as $setting => $value) {
                zone_util::players()->set_setting($user_id, $setting, $value);
            }

            return zone_util::players()->get_settings($user_id);
        });
    }

    public function me_set_language(): JsonResponse
    {
        return $this->respond(function () {
            $data = self::get_request_data();
            $lang = $data['lang'] ?? 'de';
            $lang = \in_array($lang, ['de', 'en'], true) ? $lang : 'de';

            zone_util::players()->set_player_language($this->get_user_id(), $lang);

            return [];
        });
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
        return $this->respond(function () {
            zone_util::players()->logout_players($this->get_user_id());
            return [];
        }, [
            acl::u_zone_login => 'NCZONE_REASON_NOT_ALLOWED_TO_LOGIN',
        ]);
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
        return $this->respond(function () {
            if (zone_util::players()->in_match($this->get_user_id())) {
                throw new BadRequestError('NCZONE_ALREADY_IN_A_MATCH');
            }
            zone_util::players()->login_player($this->get_user_id());
            return [];
        }, [
            acl::u_zone_login => 'NCZONE_REASON_NOT_ALLOWED_TO_LOGIN',
        ]);
    }

    /**
     * Log someone into teh zone
     *
     * @route /nczone/api/players/login
     *
     * @param int $user_id
     * @return JsonResponse
     */
    public function player_login(int $user_id): JsonResponse
    {
        return $this->respond(function ($args) {
            if (!self::is_activated($args['player_id'])) {
                throw new ForbiddenError('NCZONE_REASON_NOT_AN_ACTIVATED_PLAYER');
            }
            zone_util::players()->login_player($args['player_id']);

            $player = zone_util::players()->get_player((int)$args['player_id']);
            $logs = zone_util::logs();
            $logs->log_mod_action($logs::LOGGED_IN_PLAYER, [$player->get_username()]);

            return [];
        }, [
            acl::m_zone_login_players => 'NCZONE_REASON_NOT_ALLOWED_TO_LOGIN',
        ], [
            'player_id' => $user_id
        ]);
    }

    /**
     * Log someone out of teh zone
     *
     * @route /nczone/api/players/logout
     *
     * @param int $user_id
     * @return JsonResponse
     */
    public function player_logout(int $user_id): JsonResponse
    {
        return $this->respond(function ($args) {
            if (!self::is_activated($args['player_id'])) {
                throw new ForbiddenError('NCZONE_REASON_NOT_AN_ACTIVATED_PLAYER');
            }
            zone_util::players()->logout_players($args['player_id']);

            $player = zone_util::players()->get_player((int)$args['player_id']);
            $logs = zone_util::logs();
            $logs->log_mod_action($logs::LOGGED_OUT_PLAYER, [$player->get_username()]);

            return [];
        }, [
            acl::m_zone_login_players => 'NCZONE_REASON_NOT_ALLOWED_TO_LOGIN',
        ], [
            'player_id' => $user_id
        ]);
    }

    public function draw_blocked_until(): JsonResponse
    {
        return $this->respond(function () {
            return ['draw_blocked_until' => (int) $this->config->get(config::draw_blocked_until)];
        }, [
            acl::u_zone_draw => 'NCZONE_REASON_NOT_ALLOWED_TO_DRAW',
        ]);
    }

    public function block_draw(): JsonResponse
    {
        return $this->respond(function () {
            zone_util::misc()->block_draw();

            $logs = zone_util::logs();
            $logs->log_mod_action($logs::LOCKED);

            return [];
        }, [
            acl::m_zone_block_draw => 'NCZONE_REASON_NOT_ALLOWED_TO_BLOCK_DRAW',
        ]);
    }

    public function unblock_draw(): JsonResponse
    {
        return $this->respond(function () {
            zone_util::misc()->unblock_draw();

            $logs = zone_util::logs();
            $logs->log_mod_action($logs::UNLOCKED);

            return [];
        }, [
            acl::m_zone_block_draw => 'NCZONE_REASON_NOT_ALLOWED_TO_BLOCK_DRAW',
        ]);
    }

    public function draw_preview(): JsonResponse
    {
        return $this->respond(function () {
            if ((int) $this->config->get(config::draw_blocked_until) >= time()) {
                throw new ForbiddenError('NCZONE_REASON_NOT_ALLOWED_TO_DRAW');
            }

            $player_list = zone_util::matches()->start_draw_process($this->get_user_id());

            $logs = zone_util::logs();
            $logs->log_user_action($logs::DRAW_PREVIEW);

            return $player_list;
        }, [
            acl::u_zone_draw => 'NCZONE_REASON_NOT_ALLOWED_TO_DRAW',
        ]);
    }

    public function draw_cancel(): JsonResponse
    {
        return $this->respond(function () {
            zone_util::matches()->deny_draw_process($this->get_user_id());

            $logs = zone_util::logs();
            $logs->log_user_action($logs::DRAW_ABORTED);

            return [];
        }, [
            acl::u_zone_draw => 'NCZONE_REASON_NOT_ALLOWED_TO_DRAW',
        ]);
    }

    public function draw_confirm(): JsonResponse
    {
        return $this->respond(function () {
            if ((int) $this->config->get(config::draw_blocked_until) >= time()) {
                throw new ForbiddenError('NCZONE_REASON_NOT_ALLOWED_TO_DRAW');
            }

            $match_ids = zone_util::matches()->draw($this->get_user_id());

            $logs = zone_util::logs();
            $logs->log_user_action($logs::DRAW_CONFIRMED);

            return $match_ids;
        }, [
            acl::u_zone_draw => 'NCZONE_REASON_NOT_ALLOWED_TO_DRAW',
        ]);
    }

    public function replace_preview(int $replace_user_id): JsonResponse
    {
        return $this->respond(function ($args) {
            $match_id = zone_util::players()->get_running_match_id($args['replace_user_id']);
            // todo: check here if replace_user_id is in the match
            if (!$this->can_change_match($match_id)) {
                throw new ForbiddenError('NCZONE_REASON_NOT_ALLOWED_TO_DRAW');
            }
            if (!self::is_activated($args['replace_user_id'])) {
                throw new ForbiddenError('NCZONE_REASON_NOT_AN_ACTIVATED_PLAYER');
            }

            $players = zone_util::matches()->start_draw_process($this->get_user_id());
            if (!$players) {
                throw new ForbiddenError('NCZONE_REASON_NO_ONE_LOGGED_IN');
            }

            return [
                'replace_player' => zone_util::players()->get_player($args['replace_user_id']),
                'replace_by_player' => $players[0]
            ];
        }, [], ['replace_user_id' => $replace_user_id]);
    }

    public function replace_cancel(): JsonResponse
    {
        return $this->respond(function () {
            if (!$this->can_change_any_match()) {
                throw new ForbiddenError('NCZONE_REASON_NOT_ALLOWED_TO_DRAW');
            }
            zone_util::matches()->deny_draw_process($this->get_user_id());
            return [];
        });
    }

    public function replace_confirm(int $replace_user_id): JsonResponse
    {
        return $this->respond(function ($args) {
            $match_id = zone_util::players()->get_running_match_id($args['replace_user_id']);
            // todo: check here if replace_user_id is in the match
            if (!$this->can_change_match($match_id)) {
                throw new ForbiddenError('NCZONE_REASON_NOT_ALLOWED_TO_DRAW');
            }
            if (!self::is_activated($args['replace_user_id'])) {
                throw new ForbiddenError('NCZONE_REASON_NOT_AN_ACTIVATED_PLAYER');
            }

            $res = zone_util::matches()->replace_player(
                $this->get_user_id(),
                $match_id,
                $args['replace_user_id']
            );

            $players = zone_util::players();

            $new_match_id = $res['match_id'];
            $replaced_player = $players->get_player($res['replace_player_id']);
            $new_player = $players->get_player($res['new_player_id']);

            $logs = zone_util::logs();
            $logs->log_mod_action($logs::REPLACE_PLAYER, [$replaced_player->get_username(), $new_player->get_username(), $match_id, $new_match_id]);

            return $res;
        }, [], ['replace_user_id' => $replace_user_id]);
    }

    public function add_pair_preview(int $match_id): JsonResponse
    {
        return $this->respond(function ($args) {
            if (!$this->can_change_match($args['match_id'])) {
                throw new ForbiddenError('NCZONE_REASON_NOT_ALLOWED_TO_DRAW');
            }
            $players_loggedin = zone_util::matches()->start_draw_process($this->get_user_id());
            if (\count($players_loggedin) < 2) {
                throw new ForbiddenError('NCZONE_REASON_NO_ONE_LOGGED_IN');
            }
            $player1_id = $players_loggedin[0]->get_id();
            $player2_id = $players_loggedin[1]->get_id();

            $players_match = zone_util::matches()->get_match_players($args['match_id']);
            if ($players_match->length() === 8) {
                throw new ForbiddenError('NCZONE_REASON_MATCH_FULL');
            }
            return [
                'add_player1' => zone_util::players()->get_player($player1_id),
                'add_player2' => zone_util::players()->get_player($player2_id)
            ];
        }, [], ['match_id' => $match_id]);
    }

    public function add_pair_cancel(): JsonResponse
    {
        return $this->respond(function () {
            if (!$this->can_change_any_match()) {
                throw new ForbiddenError('NCZONE_REASON_NOT_ALLOWED_TO_DRAW');
            }
            zone_util::matches()->deny_draw_process($this->get_user_id());
            return [];
        });
    }

    public function add_pair_confirm(int $match_id): JsonResponse
    {
        return $this->respond(function ($args) {
            if (!$this->can_change_match($args['match_id'])) {
                throw new ForbiddenError('NCZONE_REASON_NOT_ALLOWED_TO_DRAW');
            }
            $players_match = zone_util::matches()->get_match_players($args['match_id']);
            if ($players_match->length() === 8) {
                throw new ForbiddenError('NCZONE_REASON_MATCH_FULL');
            }

            $res = zone_util::matches()->add_pair($this->get_user_id(), $args['match_id']);

            $players = zone_util::players();

            $new_match_id = $res['match_id'];
            $player1 = $players->get_player($res['player1_id']);
            $player2 = $players->get_player($res['player2_id']);

            $logs = zone_util::logs();
            $logs->log_mod_action($logs::ADD_PAIR, [$player1->get_username(), $player2->get_username(), $args['match_id'], $new_match_id]);

            return $res;
        }, [], ['match_id' => $match_id]);
    }

    /**
     * Returns the list of logged in users as json.
     *
     * @route /nczone/api/players/logged_in
     *
     * @return JsonResponse
     */
    public function logged_in_players(): JsonResponse
    {
        return $this->respond(function () {
            zone_util::players()->auto_logout();
            return zone_util::players()->get_logged_in();
        }, [
            acl::u_zone_view_login => 'NOT_ALLOWED_TO_VIEW_LOGGED_IN_PLAYERS'
        ]);
    }

    /**
     * Returns the list of all users that can play in the zone.
     *
     * @route /nczone/api/players
     *
     * @return JsonResponse
     */
    public function all_players(): JsonResponse
    {
        return $this->respond(function () {
            return zone_util::players()->get_all(1);
        });
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
        return $this->respond(function () {
            return zone_util::matches()->get_all_rmatches($this->can_view_bets(), $this->can_view_maps());
        }, [
            acl::u_zone_view_matches => 'NOT_ALLOWED_TO_VIEW_MATCHES'
        ]);
    }

    /**
     * Returns the list of past matches
     *
     * @route /nczone/api/pmatches
     *
     * @param int $page
     *
     * @return JsonResponse
     */
    public function pmatches(int $page = 0): JsonResponse
    {
        return $this->respond(function ($args) {
            return [
                'items' => zone_util::matches()->get_pmatches(
                    $args['page'],
                    $this->can_view_bets(),
                    $this->can_view_maps()
                ),
                'total_pages' => zone_util::matches()->get_pmatches_total_pages()
            ];
        }, [
            acl::u_zone_view_matches => 'NOT_ALLOWED_TO_VIEW_MATCHES'
        ], [
            'page' => $page
        ]);
    }

    public function match(int $match_id): JsonResponse
    {
        return $this->respond(function ($args) {
            return zone_util::matches()->get_match(
                $args['match_id'],
                $this->can_view_bets(),
                $this->can_view_maps()
            );
        }, [
            acl::u_zone_view_matches => 'NOT_ALLOWED_TO_VIEW_MATCHES'
        ], [
            'match_id' => $match_id,
        ]);
    }

    public function match_bet(int $match_id): JsonResponse
    {
        return $this->respond(function ($args) {
            $data = self::get_request_data();
            if (!isset($data['team'])) {
                throw new BadRequestError('team is not set');
            }

            $bets = zone_util::bets();
            $matches = zone_util::matches();

            if ($bets->has_bet($this->get_user_id(), $args['match_id'])) {
                throw new BadRequestError('already bet');
            }

            if ($matches->is_over($args['match_id'])) {
                throw new BadRequestError('match is over');
            }

            $team_ids = $matches->get_match_team_ids((int) $args['match_id']);
            $team_id = $team_ids[(int) $data['team'] - 1];
            $bets->place_bet($this->get_user_id(), $team_id);
            return [];
        }, [
            acl::u_zone_bet => 'NCZONE_REASON_NOT_ALLOWED_TO_BET',
        ], [
            'match_id' => $match_id,
        ]);
    }

    public function match_post_result(int $match_id): JsonResponse
    {
        return $this->respond(function ($args) {
            $data = self::get_request_data();
            if (!isset($data['winner'])) {
                throw new BadRequestError('winner is not set');
            }

            if (!$this->has_permission(acl::m_zone_draw_match) &&
                !zone_util::matches()->is_player_in_match($this->get_user_id(), $args['match_id'])
            ) {
                throw new ForbiddenError('NCZONE_REASON_NOT_ALLOWED_TO_POST_OTHER_RESULT');
            }

            $winner = (int)$data['winner'];

            zone_util::matches()->post(
                $args['match_id'],
                $this->get_user_id(),
                $winner
            );

            zone_util::misc()->block_draw_after_match();

            $logs = zone_util::logs();
            $logs->log_user_action($logs::POSTED_MATCH, [$args['match_id'], $winner]);

            return [];
        }, [
            acl::u_zone_draw => 'NCZONE_REASON_NOT_ALLOWED_TO_POST_RESULT',
        ], [
            'match_id' => $match_id,
        ]);
    }

    public function information(): JsonResponse
    {
        return $this->respond(function () {
            if (!$this->has_permission(acl::u_zone_view_info)) {
                return [];
            }
            return zone_util::misc()->get_information_posts();
        });
    }

    public function rules(): JsonResponse
    {
        return $this->respond(function () {
            return ['post' => zone_util::misc()->get_rules_post()];
        });
    }

    public function player_details(int $user_id): JsonResponse
    {
        return $this->respond(function ($args) {
            $players = zone_util::players();
            return [
                'player' => $players->get_player($args['user_id']),
                'details' => $players->get_player_details($args['user_id']),
                'ratingData' => $players->get_player_rating_data($args['user_id']),
                'dreamteams' => $players->get_player_dreamteams($args['user_id'], 5),
                'nightmareteams' => $players->get_player_nightmareteams($args['user_id'], 5),
            ];
        }, [], [
            'user_id' => $user_id,
        ]);
    }

    public function player_dreamteams(
        int $user_id,
        int $number
    ): JsonResponse {
        return $this->respond(function ($args) {
            return zone_util::players()->get_player_dreamteams(
                $args['user_id'],
                $args['number']
            );
        }, [], [
            'user_id' => $user_id,
            'number' => $number,
        ]);
    }

    public function player_nightmareteams(
        int $user_id,
        int $number
    ): JsonResponse {
        return $this->respond(function ($args) {
            return zone_util::players()->get_player_nightmareteams(
                $args['user_id'],
                $args['number']
            );
        }, [], [
            'user_id' => $user_id,
            'number' => $number,
        ]);
    }

    public function rating_data(int $user_id): JsonResponse
    {
        return $this->respond(function ($args) {
            return zone_util::players()->get_player_rating_data($args['user_id']);
        }, [], [
            'user_id' => $user_id,
        ]);
    }

    public function bets(): JsonResponse
    {
        return $this->respond(function () {
            return zone_util::players()->get_bets();
        }, [
            acl::u_zone_view_bets => 'NOT_ALLOWED_TO_VIEW_BETS'
        ]);
    }

    public function maps(): JsonResponse
    {
        return $this->respond(function () {
            $maps = zone_util::maps()->get_maps(true);

            $can_manage_maps = $this->has_permission(acl::m_zone_manage_maps);
            $weighted_veto = null;
            if($can_manage_maps) {
                $wv_raw = zone_util::maps()->get_weighted_veto();
                $weighted_veto = [];
                foreach($wv_raw as $row) {
                    $weighted_veto[$row['map_id']] = $row['weighted_veto'];
                }
            }

            return \array_map(static function (map $map) use ($total_weights, $weighted_veto) {
                $map_id = $map->get_id();
                $res = [
                    'id' => $map_id,
                    'name' => $map->get_name(),
                    'weight' => $map->get_weight(),
                    'description' => $map->get_description(),
                    'image' => zone_util::maps()->get_image_url($map->get_id()),
                ];
                if($weighted_veto) {
                    $res['weighted_veto'] = $weighted_veto[$map_id];
                }
                return $res;
            }, $maps);
        }, [
            acl::u_zone_view_maps => 'NCZONE_REASON_NOT_ALLOWED_TO_VIEW_MAPS',
        ]);
    }

    public function vetos(): JsonResponse
    {
        return $this->respond(function () {
            $can_veto = $this->has_permission(acl::u_zone_veto_maps);
            if (!$can_veto)
            {
                return [
                    'vetos' => [],
                    'free_vetos' => [],
                    'vetos_available' => 0,
                ];
            }
            else
            {
                $maps = zone_util::maps();
                
                $placed_vetos = zone_util::players()->get_vetos($this->get_user_id());
                $free_vetos = $maps->get_maps_variants(...$placed_vetos);
                $vetos_left = $maps->vetos_left($placed_vetos);

                return [
                    'vetos' => $placed_vetos,
                    'free_vetos' => $free_vetos,
                    'vetos_available' => $vetos_left,
                ];
            }
        });
    }

    public function set_veto(int $map_id): JsonResponse
    {
        return $this->respond(function ($args) {
            $players = zone_util::players();
            $maps = zone_util::maps();

            $user_id = $this->get_user_id();
            $map_id = $args['map_id'];

            if(!$maps->check_if_map_is_active($map_id)) {
                throw new ForbiddenError('NCZONE_REASON_MAP_NOT_ACTIVE');
            }

            $placed_vetos = $players->get_vetos($user_id);
            $placed_vetos[] = $map_id;

            $vetos_allowed = $maps->vetos_left($placed_vetos);

            if ($vetos_allowed < 0) {
                throw new ForbiddenError('NCZONE_REASON_TOO_MANY_VETOS');
            }

            $players->set_veto($user_id, $map_id, true);
        }, [
            acl::u_zone_veto_maps => 'NCZONE_REASON_NOT_ALLOWED_TO_VETO',
        ], [
            'map_id' => $map_id
        ]);
    }

    public function remove_veto(int $map_id): JsonResponse
    {
        return $this->respond(function ($args) {
            $players = zone_util::players();

            $user_id = $this->get_user_id();
            $map_id = $args['map_id'];

            $placed_vetos = $players->get_vetos($user_id);

            if (!in_array($map_id, $placed_vetos)) {
                throw new ForbiddenError('NCZONE_REASON_NO_VETO_FOR_MAP');
            }

            unset($placed_vetos[\array_search($map_id, $placed_vetos)]);

            $vetos_allowed = zone_util::maps()->vetos_left($placed_vetos);

            if ($vetos_allowed < 0) {
                throw new ForbiddenError('NCZONE_REASON_TOO_MANY_VETOS');
            }

            $players->set_veto($user_id, $map_id, false);
        }, [
            acl::u_zone_veto_maps => 'NCZONE_REASON_NOT_ALLOWED_TO_VETO',
        ], [
            'map_id' => $map_id
        ]);
    }

    public function clear_vetos(): JsonResponse
    {
        return $this->respond(function () {
            $user_id = $this->get_user_id();
            $placed_vetos = zone_util::players()->clear_vetos($user_id);
        }, [
            acl::u_zone_veto_maps => 'NCZONE_REASON_NOT_ALLOWED_TO_VETO',
        ], [
            'map_id' => $map_id
        ]);
    }

    public function map_civs(int $map_id): JsonResponse
    {
        return $this->respond(function ($args) {
            $civs = zone_util::civs()->get_civs();
            $civ_names = [];
            foreach($civs as $civ) {
                $civ_names[$civ->get_id()] = $civ->get_name();
            }

            $resp = [];
            foreach (zone_util::maps()->get_map_civs($args['map_id']) as $map_civ) {
                $resp[] = [
                    'map_id' => $map_civ->get_map_id(),
                    'civ_id' => $map_civ->get_civ_id(),
                    'civ_name' => $civ_names[$map_civ->get_civ_id()],
                    'multiplier' => $map_civ->get_multiplier(),
                    'force_draw' => $map_civ->get_force_draw(),
                    'prevent_draw' => $map_civ->get_prevent_draw(),
                    'both_teams' => $map_civ->get_both_teams(),
                ];
            }
            return $resp;
        }, [
            acl::u_zone_view_maps => 'NCZONE_REASON_NOT_ALLOWED_TO_VIEW_MAPS',
        ], [
            'map_id' => $map_id,
        ]);
    }

    public function save_map_description(int $map_id): JsonResponse
    {
        return $this->respond(function ($args) {
            $data = self::get_request_data();
            $description = $data['description'];

            zone_util::maps()->set_map_description($args['map_id'], $description);

            return [];
        }, [
            acl::m_zone_manage_maps => 'NCZONE_REASON_NOT_ALLOWED_TO_MANAGE_MAPS',
        ], [
            'map_id' => $map_id
        ]);
    }

    public function save_map_image(int $map_id): JsonResponse
    {
        return $this->respond(function ($args) {
            $data = self::get_request_data();
            $image_raw = $data['image'];

            zone_util::maps()->set_map_image($args['map_id'], $image_raw);

            return [];
        }, [
            acl::m_zone_manage_maps => 'NCZONE_REASON_NOT_ALLOWED_TO_MANAGE_MAPS',
        ], [
            'map_id' => $map_id
        ]);
    }

    public function statistics(int $limit): JsonResponse
    {
        return $this->respond(function ($args) {
            $players = zone_util::players();
            return [
                'best_streaks' => $players->get_best_streaks($args['limit']),
                'worst_streaks' => $players->get_worst_streaks($args['limit']),
                'best_rating_changes' => $players->get_best_rating_changes($args['limit']),
                'worst_rating_changes' => $players->get_worst_rating_changes($args['limit']),
            ];
        }, [], [
            'limit' => $limit,
        ]);
    }

    /**
     * Determine if the user is allowed to change matches in general.
     *
     * @return bool
     */
    private function can_change_any_match(): bool
    {
        return $this->can_change_match(0);
    }

    /**
     * Determine if the user is allowed to change a certain match.
     *
     * @param int $match_id
     *
     * @return bool
     */
    private function can_change_match(int $match_id): bool
    {
        if ($this->has_permission(acl::m_zone_change_match)) {
            return true;
        }

        if ($this->has_permission(acl::u_zone_change_match)) {
            return !$match_id || $this->get_user_id() === zone_util::matches()->get_draw_user_id($match_id);
        }

        return false;
    }

    /**
     * Determine if the user is allowed to view map details.
     *
     * @return bool
     */
    private function can_view_bets(): bool
    {
        return $this->has_permission(acl::u_zone_view_bets);
    }

    /**
     * Determine if the user is allowed to view map details.
     *
     * @return bool
     */
    private function can_view_maps(): bool
    {
        return $this->has_permission(acl::u_zone_view_maps);
    }

    /**
     * Determine if the user has a certain permission.
     *
     * @param string $permission
     *
     * @return bool
     */
    private function has_permission(string $permission): bool
    {
        return $this->acl->has_permission(
            self::is_activated($this->get_user_id()),
            $permission
        );
    }

    private static function get_request_data(): array
    {
        return \json_decode(\file_get_contents('php://input'), true) ?: [];
    }

    private function get_user_id(): int
    {
        return (int) $this->user->data['user_id'];
    }

    private static function is_activated(int $user_id): bool
    {
        static $is_activated = [];
        if (!isset($is_activated[$user_id])) {
            $is_activated[$user_id] = zone_util::players()->is_activated($user_id);
        }
        return $is_activated[$user_id];
    }
}
