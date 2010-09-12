<?php

namespace PSQL;

use \ParseInContext\Context as BaseContext;

class Context extends BaseContext
{
    protected $_latestAttributes = array();
    
    protected $_latestModifiers = array();
    
    public function tokenAttribute($value)
    {
        $this->_latestAttributes[substr($value, 1)] = $this->enterContext('Attribute');
    }
    
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
    
    protected function _resetLatests()
    {
        $this->_latestAttributes = array();
        $this->_latestModifiers = array();
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
        throw new ParserException("Syntax error, unexpected token $token");
    }
}
