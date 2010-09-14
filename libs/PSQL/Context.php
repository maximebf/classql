<?php

namespace PSQL;

use ParseInContext\Context as BaseContext,
    PSQL\ParserException;

class Context extends BaseContext
{
    protected $_latestModifiers = array();
    
    public function tokenStatic($value)
    {
        $this->_latestModifiers[] = $value;
    }
    
    public function tokenAbstract($value)
    {
        $this->_latestModifiers[] = $value;
    }
    
    public function tokenPrivate($value)
    {
        $this->_latestModifiers[] = $value;
    }
    
    public function tokenVirtual($value)
    {
        $this->_latestModifiers[] = $value;
    }
    
    protected function _resetModifiers()
    {
        $this->_latestModifiers = array();
    }
    
    public function tokenComment() {
        $this->enterContext('Comment');
    }
    
    public function tokenCommentOpen()
    {
        $this->enterContext('MultilineComment');
    }
    
    public function tokenEol()
    {
        
    }
    
    public function tokenWhitespace()
    {
        
    }
    
    public function __call($method, $args)
    {
        $this->_syntaxError(lcfirst(substr($method, 5)));
    }
    
    protected function _syntaxError($token)
    {
        throw new ParserException("Syntax error, unexpected token '$token' in context '" . get_class($this) . "'");
    }
}
