<?php

namespace eru\nczone\tests\config;

use eru\nczone\config\acl;
use PHPUnit\Framework\TestCase;

class acl_test extends TestCase
{
    /**
     * @dataProvider module_data_provider
     */
    public function test_module_data($expected, $permissions, $role)
    {
        $this->assertSame($expected, acl::module_data($permissions, $role));
    }

    public function module_data_provider()
    {
        return [
            [
                $expected = [
                    ['permission.add', ['some', true]],
                    ['permission.add', ['per', true]],
                    ['permission.add', ['mi', true]],
                    ['permission.add', ['sions', true]],

                    ['permission.role_add', ['role title', 'prefix_', 'some description']],
                    ['permission.permission_set', ['role title', 'some']],
                    ['permission.permission_set', ['role title', 'per']],
                    ['permission.permission_set', ['role title', 'mi']],
                    ['permission.permission_set', ['role title', 'sions']],
                ],
                $permissions = ['some', 'per', 'mi', 'sions'],
                $role = ['role title', 'prefix_', 'some description'],
            ],
            [
                $expected = [
                    ['permission.add', ['m_zone_manage_players', true]],
                    ['permission.add', ['m_zone_manage_civs', true]],
                    ['permission.add', ['m_zone_manage_maps', true]],
                    ['permission.add', ['m_zone_create_maps', true]],
                    ['permission.add', ['m_zone_draw_match', true]],
                    ['permission.add', ['m_zone_login_players', true]],
                    ['permission.add', ['m_zone_change_match', true]],

                    ['permission.role_add', ['nC Zone Mod', 'm_', 'A moderator role for the nC Zone.']],
                    ['permission.permission_set', ['nC Zone Mod', 'm_zone_manage_players']],
                    ['permission.permission_set', ['nC Zone Mod', 'm_zone_manage_civs']],
                    ['permission.permission_set', ['nC Zone Mod', 'm_zone_manage_maps']],
                    ['permission.permission_set', ['nC Zone Mod', 'm_zone_create_maps']],
                    ['permission.permission_set', ['nC Zone Mod', 'm_zone_draw_match']],
                    ['permission.permission_set', ['nC Zone Mod', 'm_zone_login_players']],
                    ['permission.permission_set', ['nC Zone Mod', 'm_zone_change_match']],
                ],
                $permissions = acl::PERMISSIONS_MOD,
                $role = acl::ROLE_MOD,
            ],
            [
                $expected = [
                    ['permission.add', ['u_zone_view_info', true]],
                    ['permission.add', ['u_zone_login', true]],
                    ['permission.add', ['u_zone_view_login', true]],
                    ['permission.add', ['u_zone_draw', true]],
                    ['permission.add', ['u_zone_change_match', true]],
                    ['permission.add', ['u_zone_view_matches', true]],
                    ['permission.add', ['u_zone_view_bets', true]],
                    ['permission.add', ['u_zone_bet', true]],

                    ['permission.permission_set', ['ROLE_USER_STANDARD', 'u_zone_view_info'],],
                    ['permission.permission_set', ['ROLE_USER_STANDARD', 'u_zone_login'],],
                    ['permission.permission_set', ['ROLE_USER_STANDARD', 'u_zone_view_login'],],
                    ['permission.permission_set', ['ROLE_USER_STANDARD', 'u_zone_draw'],],
                    ['permission.permission_set', ['ROLE_USER_STANDARD', 'u_zone_change_match'],],
                    ['permission.permission_set', ['ROLE_USER_STANDARD', 'u_zone_view_matches'],],
                    ['permission.permission_set', ['ROLE_USER_STANDARD', 'u_zone_view_bets'],],
                    ['permission.permission_set', ['ROLE_USER_STANDARD', 'u_zone_bet'],],
                ],
                $permissions = acl::PERMISSIONS_USER_STANDARD,
                $role = acl::ROLE_USER_STANDARD,
            ],
        ];
    }
}
