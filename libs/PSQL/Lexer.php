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
            'as' => 'as',
            'extends' => 'extends',
            'variable' => '\$[a-z0-9A-Z_]+',
            'eol' => "\n",
            'wildcard' => '\*',
            'pointer' => '\-\>',
            'comma' => ',',
            'callback' => '[a-zA-Z0-9_]+::[a-zA-Z0-9_]+',
            'attribute' => '\@[a-zA-Z0-9_]+',
            'value' => '"((?:[^\\\]*?(?:\\\")?)*?)"',
            'string' => '[a-zA-Z0-9_]+'
        ));
    }
}
