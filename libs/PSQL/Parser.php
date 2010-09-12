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
        $raw = parent::parse($string, 'File');
        return $this->_compute($raw);
    }
    
    public function parseFile($filename)
    {
        return $this->parse(file_get_contents($filename));
    }
    
    protected function _compute($raw)
    {
        return $raw;
    }
}
