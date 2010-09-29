<?php 

require_once __DIR__ . '/bootstrap.php';

use Demo\User;

$users = User::findAll();
foreach ($users as $user) {
    echo "#$user->id: $user->firstName $user->lastName ($user->email)\n";
    foreach ($user->findMessages() as $message) {
        echo "\t#$message->id: $message->message\n";
    }
}