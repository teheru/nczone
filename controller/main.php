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

use phpbb\user;
use dmzx\mchat\core\mchat;
use eru\nczone\config\acl;
use eru\nczone\utility\phpbb_util;
use eru\nczone\utility\zone_util;
use phpbb\controller\helper;

/**
 * nC Zone main controller.
 */
class main
{
    /* @var user */
    protected $user;

    /* @var helper */
    protected $helper;

    /* @var mchat */
    protected $mchat;

    /**
     * Constructor
     *
     * @param user $user
     * @param helper $helper
     * @param mchat|null $mchat
     */
    public function __construct(user $user, helper $helper, mchat $mchat = null)
    {
        $this->user = $user;
        $this->helper = $helper;
        $this->mchat = $mchat;
    }

    public function zone()
    {
        $auth = phpbb_util::auth();
        if (acl::has_any_permission($auth, acl::PERMISSIONS_MOD)) {
            phpbb_util::template()->assign_var('U_MCP', append_sid(phpbb_util::file_url('mcp'), 'i=-eru-nczone-mcp-main_module'));
        }

        if (acl::has_any_permission($auth, acl::PERMISSIONS_ADMIN)) {
            phpbb_util::template()->assign_var('U_ACP', append_sid(phpbb_util::file_url('adm/index.php'), 'i=-eru-nczone-acp-main_module'));
        }

        $user_id = (int) $this->user->data['user_id'];
        $user_view_mchat = zone_util::players()->get_setting($user_id, 'view_mchat');

        // page_nczone function is a core hack in the mchat plugin added
        if (
            $user_view_mchat
            && $this->mchat
            && \method_exists($this->mchat, 'page_nczone')
        ) {
            $this->mchat->page_nczone();
        }
        return $this->helper->render('zone.html');
    }
}
