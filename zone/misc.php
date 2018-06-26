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
use eru\nczone\utility\phpbb_util;
use phpbb\db\driver\driver_interface;

class misc
{
    /** @var driver_interface */
    private $db;
    /** @var \phpbb\user */
    private $user;
    /** @var string */
    private $posts_table;

    public function __construct(driver_interface $db, \phpbb\user $user, string $posts_table)
    {
        $this->db = $db;
        $this->user = $user;
        $this->posts_table = $posts_table;
    }

    public function get_information_ids(): array
    {
        return array_map(function($a): int { return (int)$a; }, explode(',', phpbb_util::config()['nczone_info_posts']));
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
            $posts[(int)$r['post_id']] = generate_text_for_display($r['post_text'], $r['bbcode_uid'], $r['bbcode_bitfield'], ($r['bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES, true);
        }

        return $posts;
    }

    public function create_post(string $title, string $message, int $forum_id): int
    {
        include_once(phpbb_util::file_url('includes/functions_posting'));

        $uid = $bitfield = $options = '';
        generate_text_for_storage($message, $uid, $bitfield, $options, true, true, false);

        $data = array(
                'topic_title' => $title,
                'post_time' => time(),
                'poster_ip' => $this->user->ip,
                'force_approved_state' => true,
                'enable_bbcode' => true,
                'enable_smilies' => false,
                'enable_urls' => true,
                'enable_sig' => false,
                'message' => $message,
                'message_md5' => md5($message),
                'post_edit_locked' => true,
                'forum_id' => $forum_id,
                'icon_id' => 0,
                'bbcode_bitfield' => $bitfield,
                'bbcode_uid' => $uid,
        );
        $poll = [];
        submit_post('post', $title, $this->user->data['username'], POST_NORMAL, $poll, $data);
        
        return (int)$data['topic_id'];
    }
}

?>