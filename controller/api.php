<?php

namespace eru\nczone\controller;

use eru\nczone\config\config;
use eru\nczone\utility\phpbb_util;
use eru\nczone\utility\zone_util;
use phpbb\auth\auth;
use phpbb\request\request_interface;
use phpbb\user;
use Symfony\Component\HttpFoundation\Response;
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

    private function optionsResponse(): JsonResponse
    {
        return new JsonResponse([], self::CODE_OK, [
            'Access-Control-Allow-Origin' => phpbb_util::request()->header('Origin') ?: '*',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Headers' => 'x-update-session',
            'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS, PUT',
        ]);
    }

    private function jsonResponse(array $data, int $code = self::CODE_OK): JsonResponse
    {
        return new JsonResponse($data, $code, [
            'Access-Control-Allow-Origin' => phpbb_util::request()->header('Origin') ?: '*',
            'Access-Control-Allow-Credentials' => 'true',
        ]);
    }

    private function errorResponse(\Throwable $t): JsonResponse
    {
        return $this->jsonResponse([
            'error' => get_class($t),
            'message' => $t->getMessage(),
        ], self::CODE_INTERNAL_SERVER_ERROR);
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
        if (self::is_options()) {
            return $this->optionsResponse();
        }

        if (self::is_update_session_request()) {
            $this->user->update_session_infos();
        }

        $user_id = (int)$this->user->data['user_id'];

        $is_activated = zone_util::players()->is_activated($user_id);

        return $this->jsonResponse([
            'id' => $user_id,
            'sid' => $this->user->session_id,
            'lang' => $this->user->data['user_lang'],
            'permissions' => [
                'u_zone_view_login' => (bool)$this->auth->acl_get('u_zone_view_login'),
                'u_zone_view_info' => (bool)$this->auth->acl_get('u_zone_view_info'),
                'u_zone_draw' => $is_activated && $this->auth->acl_get('u_zone_draw'),
                'u_zone_login' => $is_activated && $this->auth->acl_get('u_zone_login'),
                'u_zone_change_match' => $is_activated && $this->auth->acl_get('u_zone_change_match'),
                'm_zone_draw_match' => (bool)$this->auth->acl_get('m_zone_draw_match'),
                'm_zone_login_players' => (bool)$this->auth->acl_get('m_zone_login_players'),
                'm_zone_change_match' => (bool)$this->auth->acl_get('m_zone_change_match'),
            ],
        ]);
    }

    public function me_set_language(): JsonResponse
    {
        if (self::is_options()) {
            return $this->optionsResponse();
        }

        if (self::is_update_session_request()) {
            $this->user->update_session_infos();
        }

        $data = self::get_request_data();
        $lang = $data['lang'] ?? 'de';
        $lang = \in_array($lang, ['de', 'en'], true) ? $lang : 'de';

        $user_id = (int)$this->user->data['user_id'];

        zone_util::players()->set_player_language($user_id, $lang);

        return $this->jsonResponse([]);
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
        if (self::is_options()) {
            return $this->optionsResponse();
        }

        if (self::is_update_session_request()) {
            $this->user->update_session_infos();
        }

        # check is on login. used for logout as well.
        if (!$this->auth->acl_get('u_zone_login')) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_ALLOWED_TO_LOGIN'], self::CODE_FORBIDDEN);
        }

        $user_id = (int)$this->user->data['user_id'];

        if (!zone_util::players()->is_activated($user_id)) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_AN_ACTIVATED_PLAYER'], self::CODE_FORBIDDEN);
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
        if (self::is_options()) {
            return $this->optionsResponse();
        }

        if (self::is_update_session_request()) {
            $this->user->update_session_infos();
        }

        if (!$this->auth->acl_get('u_zone_login')) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_ALLOWED_TO_LOGIN'], self::CODE_FORBIDDEN);
        }

        $user_id = (int)$this->user->data['user_id'];

        if (!zone_util::players()->is_activated($user_id)) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_AN_ACTIVATED_PLAYER'], self::CODE_FORBIDDEN);
        }

        if (zone_util::players()->in_match($user_id)) {
            return $this->jsonResponse(['reason' => 'NCZONE_ALREADY_IN_A_MATCH'], self::CODE_BAD_REQUEST);
        }

        zone_util::players()->login_player($user_id);
        return $this->jsonResponse([]);
    }

    /**
     * Log someone into teh zone
     *
     * @route /nczone/api/players/login
     *
     * @return JsonResponse
     */
    public function player_login(string $user_id)
    {
        if (self::is_options()) {
            return $this->optionsResponse();
        }

        if (self::is_update_session_request()) {
            $this->user->update_session_infos();
        }

        if (!$this->auth->acl_get('m_zone_login_players')) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_ALLOWED_TO_LOGIN'], self::CODE_FORBIDDEN);
        }

        if (!zone_util::players()->is_activated((int)$user_id)) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_AN_ACTIVATED_PLAYER'], self::CODE_FORBIDDEN);
        }

        zone_util::players()->login_player((int)$user_id);
        return $this->jsonResponse([]);
    }

    /**
     * Log someone out of teh zone
     *
     * @route /nczone/api/players/logout
     *
     * @return JsonResponse
     */
    public function player_logout(string $user_id)
    {
        if (self::is_options()) {
            return $this->optionsResponse();
        }

        if (self::is_update_session_request()) {
            $this->user->update_session_infos();
        }

        if (!$this->auth->acl_get('m_zone_login_players')) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_ALLOWED_TO_LOGIN'], self::CODE_FORBIDDEN);
        }

        if (!zone_util::players()->is_activated((int)$user_id)) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_AN_ACTIVATED_PLAYER'], self::CODE_FORBIDDEN);
        }

        zone_util::players()->logout_player((int)$user_id);
        return $this->jsonResponse([]);
    }

    public function draw_preview(): JsonResponse
    {
        if (self::is_options()) {
            return $this->optionsResponse();
        }

        if (self::is_update_session_request()) {
            $this->user->update_session_infos();
        }

        if (!$this->auth->acl_get('u_zone_draw')) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_ALLOWED_TO_DRAW'], self::CODE_FORBIDDEN);
        }

        $user_id = (int)$this->user->data['user_id'];

        if (!zone_util::players()->is_activated($user_id)) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_AN_ACTIVATED_PLAYER'], self::CODE_FORBIDDEN);
        }

        try {
            $players = zone_util::matches()->start_draw_process($user_id);
            return $this->jsonResponse($players);
        } catch (\Throwable $t) {
            return $this->errorResponse($t);
        }
    }

    public function draw_cancel(): JsonResponse
    {
        if (self::is_options()) {
            return $this->optionsResponse();
        }

        if (self::is_update_session_request()) {
            $this->user->update_session_infos();
        }

        if (!$this->auth->acl_get('u_zone_draw')) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_ALLOWED_TO_DRAW'], self::CODE_FORBIDDEN);
        }

        $user_id = (int)$this->user->data['user_id'];

        if (!zone_util::players()->is_activated($user_id)) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_AN_ACTIVATED_PLAYER'], self::CODE_FORBIDDEN);
        }

        try {
            zone_util::matches()->deny_draw_process($user_id);
            return $this->jsonResponse([]);
        } catch (\Throwable $t) {
            return $this->errorResponse($t);
        }
    }

    public function draw_confirm(): JsonResponse
    {
        if (self::is_options()) {
            return $this->optionsResponse();
        }

        if (self::is_update_session_request()) {
            $this->user->update_session_infos();
        }

        if (!$this->auth->acl_get('u_zone_draw')) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_ALLOWED_TO_DRAW'], self::CODE_FORBIDDEN);
        }

        $user_id = (int)$this->user->data['user_id'];

        if (!zone_util::players()->is_activated($user_id)) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_AN_ACTIVATED_PLAYER'], self::CODE_FORBIDDEN);
        }

        try {
            return $this->jsonResponse(zone_util::matches()->draw($user_id));
        } catch (\Throwable $t) {
            return $this->errorResponse($t);
        }
    }


    public function replace_preview(int $replace_user_id): JsonResponse
    {
        if (self::is_options()) {
            return $this->optionsResponse();
        }

        if (self::is_update_session_request()) {
            $this->user->update_session_infos();
        }

        if (!$this->auth->acl_get('m_zone_change_match')) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_ALLOWED_TO_DRAW'], self::CODE_FORBIDDEN);
        }

        $user_id = (int)$this->user->data['user_id'];

        if (!zone_util::players()->is_activated($user_id)) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_AN_ACTIVATED_PLAYER'], self::CODE_FORBIDDEN);
        }

        try {
            $players = zone_util::matches()->start_draw_process($user_id);
            if (!$players) {
                return $this->jsonResponse(['reason' => 'NCZONE_REASON_NO_ONE_LOGGED_IN'], self::CODE_FORBIDDEN);
            }

            return $this->jsonResponse([
                'replace_player' => zone_util::players()->get_player($replace_user_id),
                'replace_by_player' => $players[0]
            ]);
        } catch (\Throwable $t) {
            return $this->errorResponse($t);
        }
    }

    public function replace_cancel(): JsonResponse
    {
        if (self::is_options()) {
            return $this->optionsResponse();
        }

        if (self::is_update_session_request()) {
            $this->user->update_session_infos();
        }

        if (!$this->auth->acl_get('m_zone_change_match')) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_ALLOWED_TO_DRAW'], self::CODE_FORBIDDEN);
        }

        $user_id = (int)$this->user->data['user_id'];

        if (!zone_util::players()->is_activated($user_id)) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_AN_ACTIVATED_PLAYER'], self::CODE_FORBIDDEN);
        }

        try {
            zone_util::matches()->deny_draw_process($user_id);
            return $this->jsonResponse([]);
        } catch (\Throwable $t) {
            return $this->errorResponse($t);
        }
    }

    public function replace_confirm(int $replace_user_id): JsonResponse
    {
        if (self::is_options()) {
            return $this->optionsResponse();
        }

        if (self::is_update_session_request()) {
            $this->user->update_session_infos();
        }

        if (!$this->auth->acl_get('m_zone_change_match')) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_ALLOWED_TO_DRAW'], self::CODE_FORBIDDEN);
        }

        $user_id = (int)$this->user->data['user_id'];

        if (!zone_util::players()->is_activated($user_id)) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_AN_ACTIVATED_PLAYER'], self::CODE_FORBIDDEN);
        }

        $match_id = zone_util::players()->get_running_match_id($replace_user_id);
        // todo: check here if replace_user_id is in the match

        try {
            return $this->jsonResponse(zone_util::matches()->replace_player($user_id, $match_id, $replace_user_id));
        } catch (\Throwable $t) {
            return $this->errorResponse($t);
        }
    }


    public function add_pair_preview($match_id): JsonResponse
    {
        if (self::is_options()) {
            return $this->optionsResponse();
        }

        if (self::is_update_session_request()) {
            $this->user->update_session_infos();
        }

        if (!$this->auth->acl_get('m_zone_change_match')) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_ALLOWED_TO_DRAW'], self::CODE_FORBIDDEN);
        }

        $user_id = (int)$this->user->data['user_id'];

        if (!zone_util::players()->is_activated($user_id)) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_AN_ACTIVATED_PLAYER'], self::CODE_FORBIDDEN);
        }

        try {
            $players_loggedin = zone_util::matches()->start_draw_process($user_id);
            if (\count($players_loggedin) < 2) {
                return $this->jsonResponse(['reason' => 'NCZONE_REASON_NO_ONE_LOGGED_IN'], self::CODE_FORBIDDEN); // TODO
            }
            $player1_id = $players_loggedin[0]->get_id();
            $player2_id = $players_loggedin[1]->get_id();

            $players_match = zone_util::matches()->get_match_players($match_id);
            if($players_match->length() == 8) {
                return $this->jsonResponse(['reason' => 'NCZONE_REASON_MATCH_FULL'], self::CODE_FORBIDDEN);
            }

            return $this->jsonResponse([
                'add_player1' => zone_util::players()->get_player($player1_id),
                'add_player2' => zone_util::players()->get_player($player2_id)
            ]);
        } catch (\Throwable $t) {
            return $this->errorResponse($t);
        }
    }

    public function add_pair_cancel(): JsonResponse
    {
        if (self::is_options()) {
            return $this->optionsResponse();
        }

        if (self::is_update_session_request()) {
            $this->user->update_session_infos();
        }

        if (!$this->auth->acl_get('m_zone_change_match')) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_ALLOWED_TO_DRAW'], self::CODE_FORBIDDEN);
        }

        $user_id = (int)$this->user->data['user_id'];

        if (!zone_util::players()->is_activated($user_id)) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_AN_ACTIVATED_PLAYER'], self::CODE_FORBIDDEN);
        }

        try {
            zone_util::matches()->deny_draw_process($user_id);
            return $this->jsonResponse([]);
        } catch (\Throwable $t) {
            return $this->errorResponse($t);
        }
    }

    public function add_pair_confirm($match_id): JsonResponse
    {
        if (self::is_options()) {
            return $this->optionsResponse();
        }

        if (self::is_update_session_request()) {
            $this->user->update_session_infos();
        }

        if (!$this->auth->acl_get('m_zone_change_match')) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_ALLOWED_TO_DRAW'], self::CODE_FORBIDDEN);
        }

        $user_id = (int)$this->user->data['user_id'];

        if (!zone_util::players()->is_activated($user_id)) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_AN_ACTIVATED_PLAYER'], self::CODE_FORBIDDEN);
        }

        $players_match = zone_util::matches()->get_match_players($match_id);
        if($players_match->length() == 8) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_MATCH_FULL'], self::CODE_FORBIDDEN);
        }

        try {
            return $this->jsonResponse(zone_util::matches()->add_pair($user_id, $match_id));
        } catch (\Throwable $t) {
            return $this->errorResponse($t);
        }
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
        if (self::is_options()) {
            return $this->optionsResponse();
        }

        if (self::is_update_session_request()) {
            $this->user->update_session_infos();
        }

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
        if (self::is_options()) {
            return $this->optionsResponse();
        }

        if (self::is_update_session_request()) {
            $this->user->update_session_infos();
        }

        $all_players = zone_util::players()->get_all(1); // todo: replace min matches by something with activity
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
        if (self::is_options()) {
            return $this->optionsResponse();
        }

        if (self::is_update_session_request()) {
            $this->user->update_session_infos();
        }

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
        if (self::is_options()) {
            return $this->optionsResponse();
        }

        if (self::is_update_session_request()) {
            $this->user->update_session_infos();
        }

        $rmatches = zone_util::matches()->get_pmatches();
        return $this->jsonResponse($rmatches);
    }

    public function match(string $match_id): JsonResponse
    {
        if (self::is_options()) {
            return $this->optionsResponse();
        }

        if (self::is_update_session_request()) {
            $this->user->update_session_infos();
        }

        $match = zone_util::matches()->get_match((int)$match_id);
        return $this->jsonResponse($match);
    }

    public function match_bet(string $match_id): JsonResponse
    {
        if (self::is_options()) {
            return $this->optionsResponse();
        }

        if (self::is_update_session_request()) {
            $this->user->update_session_infos();
        }

        if (!$this->auth->acl_get('u_zone_bet')) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_ALLOWED_TO_BET'], self::CODE_FORBIDDEN);
        }

        $data = self::get_request_data();
        if (!isset($data['team'])) {
            return $this->jsonResponse(['reason' => 'team is not set'], self::CODE_BAD_REQUEST);
        }

        $user_id = (int)$this->user->data['user_id'];

        if (zone_util::players()->has_bet($user_id, (int)$match_id)) {
            return $this->jsonResponse(['reason' => 'already bet'], self::CODE_BAD_REQUEST);
        }

        if (zone_util::matches()->is_over((int)$match_id)) {
            return $this->jsonResponse(['reason' => 'match is over'], self::CODE_BAD_REQUEST);
        }

        zone_util::players()->place_bet($user_id, (int)$match_id, (int)$data['team']);
        return $this->jsonResponse([]);
    }

    public function match_post_result(string $match_id): JsonResponse
    {
        if (self::is_options()) {
            return $this->optionsResponse();
        }

        if (self::is_update_session_request()) {
            $this->user->update_session_infos();
        }

        if (!$this->auth->acl_get('u_zone_draw')) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_ALLOWED_TO_POST_RESULT'], self::CODE_FORBIDDEN);
        }

        $data = self::get_request_data();
        if (!isset($data['winner'])) {
            return $this->jsonResponse(['reason' => 'winner is not set'], self::CODE_BAD_REQUEST);
        }

        $user_id = (int)$this->user->data['user_id'];

        if (!$this->auth->acl_get('m_zone_draw_match') &&
            !zone_util::matches()->is_player_in_match($user_id, (int)$match_id)
        ) {
            return $this->jsonResponse(['reason' => 'NCZONE_REASON_NOT_ALLOWED_TO_POST_OTHER_RESULT'], self::CODE_FORBIDDEN);
        }
        $winner = (int)$data['winner'];

        try {
            zone_util::matches()->post((int)$match_id, $user_id, $winner);
            return $this->jsonResponse([]);
        } catch (\Throwable $t) {
            return $this->errorResponse($t);
        }
    }

    public function information(): JsonResponse
    {
        if (self::is_options()) {
            return $this->optionsResponse();
        }

        if (self::is_update_session_request()) {
            $this->user->update_session_infos();
        }

        if (!$this->auth->acl_get('u_zone_view_info')) { // todo: this does not work
            return $this->jsonResponse([]);
        }

        $misc = zone_util::misc();
        $post_ids = $misc->get_information_ids();
        return $this->jsonResponse(array_values($misc->get_posts(...$post_ids)));
    }

    public function rules(): JsonResponse
    {
        $rulesPosts = zone_util::misc()->get_posts((int)config::get('nczone_rules_post_id'));
        return $this->jsonResponse(['post' => end($rulesPosts)]);
    }

    public function player_details(int $user_id): JsonResponse
    {
        return $this->jsonResponse(zone_util::players()->get_player_details($user_id));
    }

    public function player_dreamteams(int $user_id, int $reverse, int $number): JsonResponse
    {
        return $this->jsonResponse(zone_util::players()->get_player_dreamteams($user_id, $reverse==1, $number));
    }

    public function rating_data(int $user_id): JsonResponse
    {
        return $this->jsonResponse(zone_util::players()->get_player_rating_data($user_id));
    }

    private static function get_request_data(): array
    {
        return json_decode(file_get_contents('php://input'), true) ?: [];
    }

    private static function is_options(): bool
    {
        $server = phpbb_util::request()->get_super_global(request_interface::SERVER);
        return isset($server['REQUEST_METHOD']) && $server['REQUEST_METHOD'] === 'OPTIONS';
    }

    private static function is_update_session_request(): bool
    {
        return phpbb_util::request()->header('x-update-session') === '1';
    }
}
