<?php 

require_once __DIR__ . '/bootstrap.php';

use Demo\User;

$opts = getopt('', array('id:', 'email:', 'firstname:', 'lastname:', 'password:'));

if (empty($opts['id']) || ($user = User::findById($opts['id'])) === false) {
    exit("User not found\n");
}

if (!empty($opts['email'])) $user->email = $opts['email'];
if (!empty($opts['firstname'])) $user->firstName = $opts['firstname'];
if (!empty($opts['lastname'])) $user->lastName = $opts['lastname'];
if (!empty($opts['password'])) $user->password = $opts['password'];

$user->update();