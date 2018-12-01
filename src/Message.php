<?php
/**
 * Created by PhpStorm.
 * User: Shinoa
 * Date: 30.11.2018
 * Time: 13:31
 */

namespace webs_chat;


class Message
{
    public $type;
    public static $types = ['registration', 'server_to_user', 'user_to_server', 'user-to-user', 'error', 'user_list', 'getMyMessages'];
    public $text;
    public $from;
    public $to;
    
    public function __construct($type, $from, $to, $text)
    {
        $this->type = $type;
        $this->text = trim($text);
        $this->from = $from;
        $this->to = $to;
    }
}