<?php

namespace PSQL;

use \ParseInContext\StringParser,
    \ParseInContext\ContextFactory;

class Parser extends StringParser
{
    public function __construct()
    {
        parent::__construct(
            new Lexer(), 
            new ContextFactory(array('PSQL\\ParserContexts'))
        );
    }
    
    public function parse($string)
    {
        return parent::parse($string, 'File');
    }
    
    public function parseFile($filename)
    {
        return $this->parse(file_get_contents($filename));
    }
}
