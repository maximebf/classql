<?php

set_include_path(implode(PATH_SEPARATOR, array(
    __DIR__ . '/../libs',
    __DIR__ . '/../../ParseInContext/libs',
    get_include_path()
)));

require_once 'ParseInContext/ContextFactory.php';
\ParseInContext\ContextFactory::registerAutoloader();

$parser = new \PSQL\Parser();
var_dump($parser->parseFile(__DIR__ . '/../demo/User.psql'));
