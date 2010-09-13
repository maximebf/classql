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
            'comment' => '\/\/',
            'commentOpen' => '\/\*',
            'commentClose' => '\*\/',
            'static' => "\bstatic\b",
            'abstract' => "\babstract\b",
            'private' => "\bprivate\b",
            'virtual' => "\bvirtual\b",
            'namespace' => "\bnamespace\b",
            'use' => "\buse\b",
            'as' => "\bas\b",
            'extends' => "\bextends\b",
            'implements' => "\bimplements\b",
            'variable' => '\$[a-z0-9A-Z_]+',
            'semiColon' => ';',
            'eol' => "\n",
            'wildcard' => '\*',
            'pointer' => '\-\>',
            'comma' => ',',
            'callback' => '[a-zA-Z0-9_]+::[a-zA-Z0-9_]+',
            'attribute' => '\@[a-zA-Z0-9_]+',
            'value' => '"((?:[^\\\]*?(?:\\\")?)*?)"',
            'string' => '[a-zA-Z0-9_\\\]+',
            'whitespace' => "[\t\s]+"
        ));
    }
}
