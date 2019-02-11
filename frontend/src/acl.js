const permissions = {
  u_zone_view_info: 'u_zone_view_info',
  u_zone_login: 'u_zone_login',
  u_zone_view_login: 'u_zone_view_login',
  u_zone_draw: 'u_zone_draw',
  u_zone_change_match: 'u_zone_change_match',
  u_zone_view_matches: 'u_zone_view_matches',
  u_zone_view_bets: 'u_zone_view_bets',
  u_zone_bet: 'u_zone_bet',
  u_zone_view_maps: 'u_zone_view_maps',
  a_zone_manage_general: 'a_zone_manage_general',
  a_zone_manage_draw: 'a_zone_manage_draw',
  m_zone_manage_players: 'm_zone_manage_players',
  m_zone_manage_civs: 'm_zone_manage_civs',
  m_zone_manage_maps: 'm_zone_manage_maps',
  m_zone_create_maps: 'm_zone_create_maps',
  m_zone_block_draw: 'm_zone_block_draw',
  m_zone_draw_match: 'm_zone_draw_match',
  m_zone_login_players: 'm_zone_login_players',
  m_zone_change_match: 'm_zone_change_match'
}

const can = (user, permission) => user.permissions.includes(permission)

export default {
  permissions,
  can
}
