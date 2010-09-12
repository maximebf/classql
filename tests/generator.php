<?php

set_include_path(implode(PATH_SEPARATOR, array(
    __DIR__ . '/../libs',
    __DIR__ . '/../../ParseInContext/libs',
    get_include_path()
)));

require_once 'ParseInContext/ContextFactory.php';
\ParseInContext\ContextFactory::registerAutoloader();

$parser = new \PSQL\Parser();
$generator = new \PSQL\Generator();

$descriptor = $parser->parseFile($_SERVER['argv'][1]);
var_dump($generator->generate($descriptor));
