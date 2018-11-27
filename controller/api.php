<?php

namespace eru\nczone\controller;

use eru\nczone\config\acl;
use eru\nczone\utility\phpbb_util;
use eru\nczone\utility\zone_util;
use eru\nczone\zone\error\BadRequestError;
use eru\nczone\zone\error\ForbiddenError;
use phpbb\auth\auth;
use phpbb\request\request_interface;
use phpbb\user;
use Symfony\Component\HttpFoundation\JsonResponse;

class api
{
    /** @var user */
    protected $user;

    /** @var auth */
    protected $auth;

    private const CODE_FORBIDDEN = 403;
    private const CODE_OK = 200;
    private const CODE_BAD_REQUEST = 400;
    private const CODE_INTERNAL_SERVER_ERROR = 500;

    public function __construct(user $user, auth $auth)
    {
        $this->user = $user;
        $this->auth = $auth;
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
                if (!acl::has_permission($this->auth, self::is_activated($this->get_user_id()), $perm)) {
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
            return [
                'id' => $this->get_user_id(),
                'sid' => $this->user->session_id,
                'lang' => $this->user->data['user_lang'],
                'permissions' => acl::all_user_permissions(
                    $this->auth,
                    self::is_activated($this->get_user_id())
                ),
            ];
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
            zone_util::players()->logout_player($this->get_user_id());
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

            zone_util::players()->logout_player($args['player_id']);
            return [];
        }, [
            acl::m_zone_login_players => 'NCZONE_REASON_NOT_ALLOWED_TO_LOGIN',
        ], [
            'player_id' => $user_id
        ]);
    }

    public function draw_preview(): JsonResponse
    {
        return $this->respond(function () {
            return zone_util::matches()->start_draw_process($this->get_user_id());
        }, [
            acl::u_zone_draw => 'NCZONE_REASON_NOT_ALLOWED_TO_DRAW',
        ]);
    }

    public function draw_cancel(): JsonResponse
    {
        return $this->respond(function () {
            zone_util::matches()->deny_draw_process($this->get_user_id());
            return [];
        }, [
            acl::u_zone_draw => 'NCZONE_REASON_NOT_ALLOWED_TO_DRAW',
        ]);
    }

    public function draw_confirm(): JsonResponse
    {
        return $this->respond(function () {
            return zone_util::matches()->draw($this->get_user_id());
        }, [
            acl::u_zone_draw => 'NCZONE_REASON_NOT_ALLOWED_TO_DRAW',
        ]);
    }

    public function replace_preview(int $replace_user_id): JsonResponse
    {
        return $this->respond(function ($args) {
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
        }, [
            acl::m_zone_change_match => 'NCZONE_REASON_NOT_ALLOWED_TO_DRAW',
        ], [
            'replace_user_id' => $replace_user_id,
        ]);
    }

    public function replace_cancel(): JsonResponse
    {
        return $this->respond(function () {
            zone_util::matches()->deny_draw_process($this->get_user_id());
            return [];
        }, [
            acl::m_zone_change_match => 'NCZONE_REASON_NOT_ALLOWED_TO_DRAW',
        ]);
    }

    public function replace_confirm(int $replace_user_id): JsonResponse
    {
        return $this->respond(function ($args) {
            if (!self::is_activated($args['replace_user_id'])) {
                throw new ForbiddenError('NCZONE_REASON_NOT_AN_ACTIVATED_PLAYER');
            }

            $match_id = zone_util::players()->get_running_match_id($args['replace_user_id']);
            // todo: check here if replace_user_id is in the match

            return zone_util::matches()->replace_player(
                $this->get_user_id(),
                $match_id,
                $args['replace_user_id']
            );
        }, [
            acl::m_zone_change_match => 'NCZONE_REASON_NOT_ALLOWED_TO_DRAW',
        ], [
            'replace_user_id', $replace_user_id,
        ]);
    }

    public function add_pair_preview(int $match_id): JsonResponse
    {
        return $this->respond(function ($args) {
            $players_loggedin = zone_util::matches()->start_draw_process($this->get_user_id());
            if (\count($players_loggedin) < 2) {
                throw new ForbiddenError('NCZONE_REASON_NO_ONE_LOGGED_IN');
            }
            $player1_id = $players_loggedin[0]->get_id();
            $player2_id = $players_loggedin[1]->get_id();

            $players_match = zone_util::matches()->get_match_players($args['match_id']);
            if($players_match->length() === 8) {
                throw new ForbiddenError('NCZONE_REASON_MATCH_FULL');
            }

            return [
                'add_player1' => zone_util::players()->get_player($player1_id),
                'add_player2' => zone_util::players()->get_player($player2_id)
            ];
        }, [
            acl::m_zone_change_match => 'NCZONE_REASON_NOT_ALLOWED_TO_DRAW',
        ], [
            'match_id' => $match_id,
        ]);
    }

    public function add_pair_cancel(): JsonResponse
    {
        return $this->respond(function () {
            zone_util::matches()->deny_draw_process($this->get_user_id());
            return [];
        }, [
            acl::m_zone_change_match => 'NCZONE_REASON_NOT_ALLOWED_TO_DRAW',
        ]);
    }

    public function add_pair_confirm(int $match_id): JsonResponse
    {
        return $this->respond(function ($args) {
            $players_match = zone_util::matches()->get_match_players($args['match_id']);
            if($players_match->length() === 8) {
                throw new ForbiddenError('NCZONE_REASON_MATCH_FULL');
            }

            return zone_util::matches()->add_pair($this->get_user_id(), $args['match_id']);
        }, [
            acl::m_zone_change_match => 'NCZONE_REASON_NOT_ALLOWED_TO_DRAW',
        ], [
            'match_id' => $match_id,
        ]);
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
            return zone_util::players()->get_logged_in();
        });
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
            // todo: replace min matches by something with activity
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
            return zone_util::matches()->get_all_rmatches();
        });
    }

    /**
     * Returns the list of past matches
     *
     * @route /nczone/api/pmatches
     *
     * @return JsonResponse
     */
    public function pmatches(): JsonResponse
    {
        return $this->respond(function () {
            return zone_util::matches()->get_pmatches();
        });
    }

    public function match(int $match_id): JsonResponse
    {
        return $this->respond(function ($args) {
            return zone_util::matches()->get_match($args['match_id']);
        }, [], [
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

            if (zone_util::bets()->has_bet($this->get_user_id(), $args['match_id'])) {
                throw new BadRequestError('already bet');
            }

            if (zone_util::matches()->is_over($args['match_id'])) {
                throw new BadRequestError('match is over');
            }

            zone_util::bets()->place_bet(
                $this->get_user_id(),
                $args['match_id'],
                (int) $data['team']
            );
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

            if (!$this->auth->acl_get(acl::m_zone_draw_match) &&
                !zone_util::matches()->is_player_in_match($this->get_user_id(), $args['match_id'])
            ) {
                throw new ForbiddenError('NCZONE_REASON_NOT_ALLOWED_TO_POST_OTHER_RESULT');
            }
            zone_util::matches()->post(
                $args['match_id'],
                $this->get_user_id(),
                (int) $data['winner']
            );
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
            if (!$this->auth->acl_get(acl::u_zone_view_info)) { // todo: this does not work
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
        });
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
        static $is_activated;
        if ($is_activated === null) {
            $is_activated = [];
        }
        if (!isset($is_activated[$user_id])) {
            $is_activated[$user_id] = zone_util::players()->is_activated($user_id);
        }
        return $is_activated[$user_id];
    }
}
