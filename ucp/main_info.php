<?php
/**
 *
 * nC Zone. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Marian Cepok, https://new-chapter.eu
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace eru\nczone\ucp;

/**
 * nC Zone UCP module info.
 */
class main_info
{
	function module()
	{
		return array(
			'filename'	=> '\eru\nczone\ucp\main_module',
			'title'		=> 'UCP_DEMO_TITLE',
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> 'UCP_DEMO',
					'auth'	=> 'ext_eru/nczone',
					'cat'	=> array('UCP_DEMO_TITLE')
				),
			),
		);
	}
}
