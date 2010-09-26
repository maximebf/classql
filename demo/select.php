<?php 

require_once __DIR__ . '/bootstrap.php';

require_once 'classql://Models/Message.cql';

if (($user = User::findById(1)) === false) {
    exit("Not found\n");
}

var_dump($user);

$messages = $user->findMessages();
var_dump($messages);
