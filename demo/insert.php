<?php 

require_once __DIR__ . '/bootstrap.php';

use Demo\User;

$user = new User();
$user->email = 'example@example.com';
$user->password = 'azerty';
$user->firstName = 'john';
$user->lastName = 'doe';
$user->insert();

$user->addMessage('hello world');