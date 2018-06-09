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

/**
 * nC Zone matches management class.
 */
class matches {
	// TODO: attributes
	
	/**
	 * Constructor
	 * 
	 * @param \phpbb\db\driver\driver_interface    $db    Database object
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db)
	{
		$this->db = $db;
	}
}

?>
