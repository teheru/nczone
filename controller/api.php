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
        $user_id = (int)$this->user->data['user_id'];

        $is_activated = zone_util::players()->is_activated($user_id);

        return $this->jsonResponse([
            'id' => $user_id,
            'canViewLogin' => (bool)$this->auth->acl_get('u_zone_view_login'),
            'canDraw' => $is_activated && (bool)$this->auth->acl_get('u_zone_draw'),
            'canLogin' => $is_activated && (bool)$this->auth->acl_get('u_zone_login'),
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
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_ALLOWED_TO_LOGIN'], 403); // forbidden
        }

        $user_id = (int)$this->user->data['user_id'];

        if (!zone_util::players()->is_activated($user_id)) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_AN_ACTIVATED_PLAYER'], 403); // forbidden
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
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_ALLOWED_TO_LOGIN'], 403); // forbidden
        }

        $user_id = (int)$this->user->data['user_id'];

        if (!zone_util::players()->is_activated($user_id)) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_AN_ACTIVATED_PLAYER'], 403); // forbidden
        }

        zone_util::players()->login_player($user_id);
        return $this->jsonResponse([]);
    }

    public function draw_preview(): JsonResponse
    {
        if (!(bool)$this->auth->acl_get('u_zone_draw')) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_ALLOWED_TO_DRAW'], 403); // forbidden
        }

        $user_id = (int)$this->user->data['user_id'];

        if (!zone_util::players()->is_activated($user_id)) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_AN_ACTIVATED_PLAYER'], 403); // forbidden
        }

        $players = zone_util::matches()->start_draw_process($user_id);
        return $this->jsonResponse($players);
    }

    public function draw_cancel(): JsonResponse
    {
        if (!(bool)$this->auth->acl_get('u_zone_draw')) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_ALLOWED_TO_DRAW'], 403); // forbidden
        }

        $user_id = (int)$this->user->data['user_id'];

        if (!zone_util::players()->is_activated($user_id)) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_AN_ACTIVATED_PLAYER'], 403); // forbidden
        }

        zone_util::matches()->deny_draw_process($user_id);
        return $this->jsonResponse([]);
    }

    public function draw_confirm(): JsonResponse
    {
        if (!(bool)$this->auth->acl_get('u_zone_draw')) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_ALLOWED_TO_DRAW'], 403); // forbidden
        }

        $user_id = (int)$this->user->data['user_id'];

        if (!zone_util::players()->is_activated($user_id)) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_AN_ACTIVATED_PLAYER'], 403); // forbidden
        }

        $players = zone_util::matches()->confirm_draw_process($user_id);
        if (empty($players)) {
            // todo: maybe add reason or something, but use normal status code
            return $this->jsonResponse([]);
        }

        $match_ids = zone_util::matches()->draw($user_id, $players);
        return $this->jsonResponse($match_ids);
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
        $logged_in_players = zone_util::players()->get_logged_in();
        return $this->jsonResponse($logged_in_players);
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
        $all_players = zone_util::players()->get_all();
        return $this->jsonResponse($all_players);
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
        $rmatches = zone_util::matches()->get_all_rmatches();
        return $this->jsonResponse($rmatches);
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
        $rmatches = zone_util::matches()->get_all_pmatches();
        return $this->jsonResponse($rmatches);
    }

    public function information(): JsonResponse
    {
        if (!(bool)$this->auth->acl_get('u_zone_view_info')) { // todo: this does not work
            return $this->jsonResponse([]);
        }

        $post_ids = array_map(function($a) { return (int)$a; }, explode(',', phpbb_util::config()['nczone_info_posts']));
        return $this->jsonResponse(zone_util::misc()->get_posts(...$post_ids));
    }
}
