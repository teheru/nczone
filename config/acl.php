<?php

namespace eru\nczone\config;

use phpbb\auth\auth;

final class acl
{
    public const u_zone_view_info = 'u_zone_view_info';
    public const u_zone_login = 'u_zone_login';
    public const u_zone_view_login = 'u_zone_view_login';
    public const u_zone_draw = 'u_zone_draw';
    public const u_zone_change_match = 'u_zone_change_match';
    public const u_zone_view_matches = 'u_zone_view_matches';
    public const u_zone_view_bets = 'u_zone_view_bets';
    public const u_zone_bet = 'u_zone_bet';
    public const u_zone_view_maps = 'u_zone_view_maps';

    public const a_zone_manage_general = 'a_zone_manage_general';
    public const a_zone_manage_draw = 'a_zone_manage_draw';

    public const m_zone_manage_players = 'm_zone_manage_players';
    public const m_zone_manage_civs = 'm_zone_manage_civs';
    public const m_zone_manage_maps = 'm_zone_manage_maps';
    public const m_zone_create_maps = 'm_zone_create_maps';
    public const m_zone_draw_match = 'm_zone_draw_match';
    public const m_zone_login_players = 'm_zone_login_players';
    public const m_zone_change_match = 'm_zone_change_match';

    private const activation_required = [
        self::u_zone_draw,
        self::u_zone_login,
        self::u_zone_change_match,
    ];

    public const PERMISSIONS_ADMIN = [
        self::a_zone_manage_general,
        self::a_zone_manage_draw,
    ];

    public const PERMISSIONS_MOD = [
        self::m_zone_manage_players,
        self::m_zone_manage_civs,
        self::m_zone_manage_maps,
        self::m_zone_create_maps,
        self::m_zone_draw_match,
        self::m_zone_login_players,
        self::m_zone_change_match,
    ];

    public const PERMISSIONS_USER_STANDARD = [
        self::u_zone_view_info,
        self::u_zone_login,
        self::u_zone_view_login,
        self::u_zone_draw,
        self::u_zone_change_match,
        self::u_zone_view_matches,
        self::u_zone_view_bets,
        self::u_zone_bet,
        self::u_zone_view_maps,
    ];

    public const ROLE_ADMIN = [
        'nC Zone Admin',
        'a_',
        'A full administrative role for the nC Zone.',
    ];

    public const ROLE_MOD = [
        'nC Zone Mod',
        'm_',
        'A moderator role for the nC Zone.',
    ];

    public const ROLE_USER_STANDARD = [
        'ROLE_USER_STANDARD',
    ];

    private static function all_permissions(): array
    {
        static $permissions;
        if ($permissions === null) {
            $permissions = \array_merge(
                self::PERMISSIONS_USER_STANDARD,
                self::PERMISSIONS_MOD,
                self::PERMISSIONS_ADMIN
            );
        }
        return $permissions;
    }

    public static function add_categories_language($categories): array
    {
        return \array_merge($categories ?: [], [
            'zone' => 'ACP_CAT_ZONE',
        ]);
    }

    public static function add_permission_language($acl): array
    {
        return \array_merge($acl ?: [], [
            self::u_zone_view_info => ['lang' => 'ACL_U_ZONE_VIEW_INFO', 'cat' => 'zone'],
            self::u_zone_login => ['lang' => 'ACL_U_ZONE_LOGIN', 'cat' => 'zone'],
            self::u_zone_view_login => ['lang' => 'ACL_U_ZONE_VIEW_LOGIN', 'cat' => 'zone'],
            self::u_zone_draw => ['lang' => 'ACL_U_ZONE_DRAW', 'cat' => 'zone'],
            self::u_zone_view_matches => ['lang' => 'ACL_U_ZONE_VIEW_MATCHES', 'cat' => 'zone'],
            self::u_zone_view_bets => ['lang' => 'ACL_U_ZONE_VIEW_BETS', 'cat' => 'zone'],
            self::u_zone_bet => ['lang' => 'ACL_U_ZONE_BET', 'cat' => 'zone'],
            self::u_zone_view_maps => ['lang' => 'ACL_U_ZONE_VIEW_MAPS', 'cat' => 'zone'],
            self::u_zone_change_match => ['lang' => 'ACL_U_ZONE_CHANGE_MATCH', 'cat' => 'zone'],

            self::m_zone_manage_players => ['lang' => 'ACL_M_ZONE_MANAGE_PLAYERS', 'cat' => 'zone'],
            self::m_zone_manage_civs => ['lang' => 'ACL_M_ZONE_MANAGE_CIVS', 'cat' => 'zone'],
            self::m_zone_manage_maps => ['lang' => 'ACL_M_ZONE_MANAGE_MAPS', 'cat' => 'zone'],
            self::m_zone_create_maps => ['lang' => 'ACL_M_ZONE_CREATE_MAPS', 'cat' => 'zone'],
            self::m_zone_login_players => ['lang' => 'ACL_M_ZONE_LOGIN_PLAYERS', 'cat' => 'zone'],
            self::m_zone_draw_match => ['lang' => 'ACL_M_ZONE_DRAW_MATCH', 'cat' => 'zone'],
            self::m_zone_change_match => ['lang' => 'ACL_M_ZONE_CHANGE_MATCH', 'cat' => 'zone'],

            self::a_zone_manage_general => ['lang' => 'ACL_A_ZONE_MANAGE_GENERAL', 'cat' => 'zone'],
            self::a_zone_manage_draw => ['lang' => 'ACL_A_ZONE_MANAGE_DRAW', 'cat' => 'zone'],
        ]);
    }

    public static function has_any_permission(
        auth $auth,
        array $permissions
    ): bool {
        foreach ($permissions as $permission) {
            if ($auth->acl_get($permission)) {
                return true;
            }
        }
        return false;
    }

    public static function has_permission(
        auth $auth,
        bool $activated,
        string $perm
    ): bool {
        if (!$activated && \in_array($perm, self::activation_required, true)) {
            return false;
        }
        return (bool) $auth->acl_get($perm);
    }

    public static function all_user_permissions(
        auth $auth,
        bool $activated
    ): array {
        return \array_values(\array_filter(
            self::all_permissions(),
            function ($permission) use ($auth, $activated) {
                return self::has_permission($auth, $activated, $permission);
            }
        ));
    }

    public static function module_data(array $permissions, array $role): array
    {
        return \array_merge(
            \array_map(function ($permission) {
                return ['permission.add', [$permission, true]];
            }, $permissions),
            $role === self::ROLE_USER_STANDARD ? [] : [['permission.role_add', $role]],
            \array_map(function ($permission) use ($role) {
                return ['permission.permission_set', [$role[0], $permission]];
            }, $permissions)
        );
    }
}
