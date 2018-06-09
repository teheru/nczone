<?php

/**
 *
 * nC Zone. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Marian Cepok, https://new-chapter.eu
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eru\nczone\zone;

use phpbb\db\driver\driver_interface;

class matches
{
    /** @var driver_interface */
    private $db;

    public function __construct(driver_interface $db)
    {
        $this->db = $db;
    }
}
