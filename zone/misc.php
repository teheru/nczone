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

use eru\nczone\utility\db_util;
use phpbb\db\driver\driver_interface;

class misc
{
    /** @var driver_interface */
    private $db;

    /** @var string */
    private $posts_table;

    public function __construct(driver_interface $db, string $posts_table)
    {
        $this->db = $db;
        $this->posts_table = $posts_table;
    }

    public function get_post(int $post_id)
    {
        $row = db_util::get_row($this->db, [
            'SELECT' => 't.post_text, t.bbcode_uid, t.bbcode_bitfield',
            'FROM' => [$this->posts_table => 't'],
            'WHERE' => 't.post_id = ' . $post_id
        ]);

        return generate_text_for_display($row['post_text'], $row['bbcode_uid'], $row['bbcode_bitfield'], ($row['bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES, true);
    }
}

?>