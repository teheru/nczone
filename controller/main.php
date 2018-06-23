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
        return $this->helper->render('zone.html');
    }
}
