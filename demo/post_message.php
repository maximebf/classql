<?php 

require_once __DIR__ . '/bootstrap.php';

use Demo\User;

$opts = getopt('', array('to:', 'message:'));

if (empty($opts['to']) || ($user = User::findById($opts['to'])) === false) {
    exit("User not found\n");
}

$user->addMessage($opts['message']);