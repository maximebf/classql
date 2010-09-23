<?php 

require_once __DIR__ . '/init.php';

$user = new User();
$user->email = 'example@example.com';
$user->password = 'azerty';
$user->insert();

$message = new Message();
$message->user_id = $user->id;
$message->message = 'hello world';
$message->insert();
