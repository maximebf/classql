<?php

set_include_path(implode(PATH_SEPARATOR, array(
    __DIR__ . '/../libs',
    __DIR__ . '/../vendor/parsec/libs',
    get_include_path()
)));

require_once 'Parsec/Lexer.php';
require_once 'ClassQL/Parser/Lexer.php';

$lexer = new ClassQL\Parser\Lexer();
var_dump($lexer->tokenize(file_get_contents(__DIR__ . '/../demo/Message.psql')));
