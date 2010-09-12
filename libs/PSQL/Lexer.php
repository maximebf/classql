<?php

namespace PSQL;

use \ParseInContext\Lexer as BaseLexer;

class Lexer extends BaseLexer
{
    public function __construct()
    {
        parent::__construct(array(
            'parenthOpen' => '\(',
            'parenthClose' => '\)',
            'curlyOpen' => '\{',
            'curlyClose' => '\}',
            'static' => 'static',
            'abstract' => 'abstract',
            'private' => 'private',
            'virtual' => 'virtual',
            'namespace' => 'namespace',
            'as' => 'as',
            'extends' => 'extends',
            'implements' => 'implements',
            'variable' => '\$[a-z0-9A-Z_]+',
            'semiColon' => ';',
            'eol' => "\n",
            'wildcard' => '\*',
            'pointer' => '\-\>',
            'comma' => ',',
            'callback' => '[a-zA-Z0-9_]+::[a-zA-Z0-9_]+',
            'attribute' => '\@[a-zA-Z0-9_]+',
            'value' => '"((?:[^\\\]*?(?:\\\")?)*?)"',
            'string' => '[a-zA-Z0-9_]+',
            'whitespace' => "[\t\s]+"
        ));
    }
}
