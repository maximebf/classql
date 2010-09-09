<?php

set_include_path(implode(PATH_SEPARATOR, array(
    __DIR__ . '/../libs',
    __DIR__ . '/../../ParseInContext/libs',
    get_include_path()
)));

require_once 'ParseInContext/Lexer.php';
require_once 'PSQL/Lexer.php';

$lexer = new \PSQL\Lexer();
var_dump($lexer->tokenize(file_get_contents(__DIR__ . '/../demo/Message.psql')));
