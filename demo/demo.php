<?php

set_include_path(implode(PATH_SEPARATOR, array(
    __DIR__ . '/../libs',
    __DIR__ . '/../vendor/parsec/libs',
    get_include_path()
)));

require_once 'ClassQL/Session.php';
ClassQL\Session::registerAutoloader();
ClassQL\Session::registerStreamWrapper();

require_once 'psql://' . __DIR__ . '/User.psql';
require_once 'psql://' . __DIR__ . '/Message.psql';

$user = new User();
$user->email = 'example@example.com';
$user->password = md5('azerty');
$user->save();

$message = new Message();
$message->user_id = $user->id;
$message->message = 'hello world';
$message->save();

$user = User::find_by_id(1);
echo $user->email;

$messages = $user->find_messages();
