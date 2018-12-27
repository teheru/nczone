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

class install_mcp_module extends \phpbb\db\migration\migration
{
    public function effectively_installed()
    {
        $sql = <<<SQL
SELECT 
  module_id
FROM 
  {$this->table_prefix}modules
WHERE 
  module_class = 'mcp'
  AND module_langname = 'MCP_ZONE_TITLE'
SQL;
        $result = $this->db->sql_query($sql);
        $module_id = $this->db->sql_fetchfield('module_id');
        $this->db->sql_freeresult($result);

        return $module_id !== false;
    }

    static public function depends_on()
    {
        return ['\phpbb\db\migration\data\v31x\v314'];
    }

    public function update_data()
    {
        return \array_merge([
            [
                'module.add',
                [
                    'mcp',
                    0,
                    'MCP_ZONE_TITLE',
                ],
            ],
            [
                'module.add',
                [
                    'mcp',
                    'MCP_ZONE_TITLE',
                    [
                        'module_basename' => '\eru\nczone\mcp\main_module',
                        'modes' => ['players', 'civs', 'maps'],
                    ],
                ],
            ],
        ], acl::module_data(acl::PERMISSIONS_MOD, acl::ROLE_MOD));
    }
}
