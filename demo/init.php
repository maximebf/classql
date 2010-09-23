<?php

set_include_path(implode(PATH_SEPARATOR, array(
    __DIR__ . '/libs',
    __DIR__ . '/../libs',
    __DIR__ . '/../vendor/parsec/libs',
    get_include_path()
)));

require_once 'ClassQL/Session.php';
ClassQL\Session::registerAutoloader();

use ClassQL\Session,
    ClassQL\Database\Connection;

Session::configure(
    new Connection('mysql:host=192.168.56.101;dbname=classql', 'root', 'root')
);