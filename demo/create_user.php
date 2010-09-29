<?php 

require_once __DIR__ . '/bootstrap.php';

use Demo\User;

$opts = array_merge(
    array('email' => 'example@example.com', 'firstname' => 'john', 'lastname' => 'doe', 'password' => uniqid()),
    getopt('', array('email:', 'firstname:', 'lastname:', 'password:'))
);

$user = new User();
$user->email = $opts['email'];
$user->firstName = $opts['firstname'];
$user->lastName = $opts['lastname'];
$user->password = $opts['password'];
$user->insert();