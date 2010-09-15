<?php

set_include_path(implode(PATH_SEPARATOR, array(
    __DIR__ . '/../libs',
    __DIR__ . '/../vendor/parsec/libs',
    get_include_path()
)));

require_once 'ClassQL/Session.php';
ClassQL\Session::registerAutoloader();

$parser = new ClassQL\Parser();
var_dump($parser->parseFile($_SERVER['argv'][1]));
