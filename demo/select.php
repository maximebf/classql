<?php 

require_once __DIR__ . '/init.php';

$user = User::findById(1);
echo $user->email;

$messages = $user->findMessages();
var_dump($messages);
