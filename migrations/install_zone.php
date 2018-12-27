<?php
/**
 *
 * nC Zone. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Marian Cepok, https://new-chapter.eu
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eru\nczone\migrations;

use eru\nczone\config\acl;

class install_zone extends \phpbb\db\migration\migration
{
    public function effectively_installed()
    {
        // TODO
    }

    static public function depends_on()
    {
        return ['\phpbb\db\migration\data\v31x\v314'];
    }

    public function update_data()
    {
        return acl::module_data(
            acl::PERMISSIONS_USER_STANDARD,
            acl::ROLE_USER_STANDARD
        );
    }
}
