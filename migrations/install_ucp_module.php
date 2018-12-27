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

class install_ucp_module extends \phpbb\db\migration\migration
{
    public function effectively_installed()
    {
        $sql = <<<SQL
SELECT 
  module_id
FROM 
  {$this->table_prefix}modules
WHERE 
  module_class = 'ucp'
  AND module_langname = 'UCP_DEMO_TITLE'
SQL;
        $result = $this->db->sql_query($sql);
        $module_id = $this->db->sql_fetchfield('module_id');
        $this->db->sql_freeresult($result);

        return $module_id !== false;
    }

    static public function depends_on()
    {
        return ['\eru\nczone\migrations\install_db_create'];
    }

    public function update_data()
    {
        return [];
    }
}
