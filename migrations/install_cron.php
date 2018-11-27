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

use eru\nczone\config\config;

class install_cron extends \phpbb\db\migration\migration
{
    public function effectively_installed()
    {
        return isset($this->config[config::activity_cron]);
    }

    static public function depends_on()
    {
        return array('\phpbb\db\migration\data\v31x\v314');
    }

    public function update_data()
    {
        return config::module_data(config::cron_settings);
    }
}
