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

use eru\nczone\config\config;
use eru\nczone\utility\db;
use eru\nczone\utility\phpbb_util;

class misc
{
    /** @var db */
    private $db;
    /** @var \phpbb\user */
    private $user;

    public function __construct(\phpbb\user $user, db $db)
    {
        $this->db = $db;
        $this->user = $user;
    }

    public static function cut_to_same_length(array ...$arrays): array
    {
        $min = \min(\array_map('\count', $arrays));
        return \array_map(static function ($array) use ($min) {
            return \array_slice($array, 0, $min);
        }, $arrays);
    }

    public function get_rules_post(): string
    {
        return $this->get_post((int) config::get(config::rules_post_id));
    }

    public function get_information_posts(): array
    {
        $post_ids = $this->get_information_post_ids();
        return \array_values($this->get_posts(...$post_ids));
    }

    public function get_information_post_ids(): array
    {
        return array_map('\intval', explode(',', config::get(config::info_posts)));
    }

    public function get_post(int $post_id): string
    {
        $posts = $this->get_posts($post_id);
        return $posts ? \end($posts) : '';
    }

    public function get_posts(int ...$post_ids): array
    {
        $rows = $this->db->get_rows([
            'SELECT' => 't.post_id, t.post_text, t.bbcode_uid, t.bbcode_bitfield',
            'FROM' => [$this->db->posts_table => 't'],
            'WHERE' => $this->db->sql_in_set('t.post_id', $post_ids),
        ]);

        $posts = [];
        foreach ($rows as $r) {
            $posts[(int)$r['post_id']] = (string) generate_text_for_display(
                $r['post_text'],
                $r['bbcode_uid'],
                $r['bbcode_bitfield'],
                ($r['bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES,
                true
            );
        }

        return $posts;
    }

    public function create_post(string $title, string $message, int $forum_id): int
    {
        include_once phpbb_util::file_url('includes/functions_posting');

        $uid = $bitfield = $options = '';
        generate_text_for_storage($message, $uid, $bitfield, $options, true, true, false);

        $data = [
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
        ];
        $poll = [];
        submit_post('post', $title, $this->user->data['username'], POST_NORMAL, $poll, $data);

        return (int)$data['topic_id'];
    }

    public function block_draw(): void
    {
        $minutes = (int) config::get(config::draw_block_time);
        if ($minutes === 0) {
            $minutes = 60 * 24 * 365 * 10;
        }
        $this->set_draw_blocked_until(time() + $minutes * 60);
    }

    public function unblock_draw(): void
    {
        $this->set_draw_blocked_until(0);
    }

    public function block_draw_after_match(): void
    {
        $minutes = (int) config::get(config::draw_block_after_match);
        if ($minutes <= 0) {
            return;
        }

        $time = time() + $minutes * 60;
        $old_time = (float) config::get(config::draw_blocked_until);
        if ($old_time < $time) {
            $this->set_draw_blocked_until($time);
        }
    }

    private function set_draw_blocked_until(int $time): void
    {
        config::set(config::draw_blocked_until, $time);
    }
}
