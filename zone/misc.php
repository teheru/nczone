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

    public function get_posts(int ...$post_ids): array
    {
        $rows = db_util::get_rows($this->db, [
            'SELECT' => 't.post_id, t.post_text, t.bbcode_uid, t.bbcode_bitfield',
            'FROM' => [$this->posts_table => 't'],
            'WHERE' => $this->db->sql_in_set('t.post_id', $post_ids),
        ]);
        
        $numbers = array_count_values($post_ids);
        $posts = [];
        foreach($rows as $r)
        {
            $post_id = (int)$r['post_id'];
            $posts[$post_id] = [
                'number' => $numbers[$post_id],
                'info' => generate_text_for_display($r['post_text'], $r['bbcode_uid'], $r['bbcode_bitfield'], ($r['bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES, true)
            ];
        }

        return $posts;
    }
}

?>