<?php

set_include_path(implode(PATH_SEPARATOR, array(
    __DIR__ . '/../libs',
    __DIR__ . '/../vendor/parsec/libs',
    get_include_path()
)));

require_once 'ClassQL/Session.php';
ClassQL\Session::registerAutoloader();

$parser = new ClassQL\Parser\Parser();
$generator = new ClassQL\Generator\PHPGenerator();

$descriptor = $parser->parseFile($_SERVER['argv'][1]);
var_dump($generator->generate($descriptor));
