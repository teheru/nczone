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
    /* @var \phpbb\controller\helper */
    protected $helper;

    /**
     * Constructor
     *
     * @param \phpbb\controller\helper $helper
     */
    public function __construct(\phpbb\controller\helper $helper)
    {
        $this->helper = $helper;
    }

    public function zone()
    {
        if(false) // todo: any relevant m_ rights
        {
            phpbb_util::template()->assign_var('U_MCP', append_sid(phpbb_util::file_url('mcp'), 'i=-eru-nczone-mcp-main_module'));
        }
        if(false) // todo: any a_ rights
        {
            phpbb_util::template()->assign_var('U_ACP', append_sid(phpbb_util::file_url('adm/index.php'), 'i=-eru-nczone-acp-main_module'));
        }
        return $this->helper->render('zone.html');
    }
}
