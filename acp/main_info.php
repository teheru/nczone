<?php
/**
 *
 * nC Zone. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Marian Cepok, https://new-chapter.eu
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eru\nczone\acp;

/**
 * nC Zone ACP module info.
 */
class main_info
{
	public function module()
	{
		return array(
			'filename'	=> '\eru\nczone\acp\main_module',
			'title'		=> 'ACP_DEMO_TITLE',
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> 'ACP_NCZONE',
					'auth'	=> 'ext_eru/nczone && acl_a_board',
					'cat'	=> array('ACP_NCZONE_TITLE')
				),
			),
		);
	}
}
