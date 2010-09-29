<?php 

require_once __DIR__ . '/bootstrap.php';

use Demo\User;

if (($user = User::findById(1)) === false) {
    exit("Not found\n");
}

var_dump($user);

$messages = $user->findMessages();
var_dump($messages);
