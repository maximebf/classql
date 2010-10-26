<?php 

require_once __DIR__ . '/bootstrap.php';

use Demo\Message;

$opts = getopt('', array('id:'));

if (empty($opts['id']) || ($message = Message::findWithUser($opts['id'])) === false) {
    exit("Message not found\n");
}

echo "{$message->message} from {$message->user->email}\n";