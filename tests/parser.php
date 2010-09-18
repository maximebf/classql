<?php

set_include_path(implode(PATH_SEPARATOR, array(
    __DIR__ . '/../libs',
    __DIR__ . '/../vendor/parsec/libs',
    get_include_path()
)));

require_once 'ClassQL/Session.php';
ClassQL\Session::registerAutoloader();

$parser = new ClassQL\Parser\Parser();
var_dump($parser->parse(file_get_contents($_SERVER['argv'][1])));
