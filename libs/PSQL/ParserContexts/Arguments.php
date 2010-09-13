<?php

namespace PSQL\ParserContexts;

use \PSQL\Context;

class Arguments extends Context
{
    protected $_args = array();
    
    public function tokenValue($value)
    {
        if (!empty($this->_args)) {
            $this->_syntaxError('value');
        }
        
        $this->_args[] = array(
            'type' => 'scalar', 
            'value' => str_replace('\\"', '"', trim($value, '"'))
        );
    }
    
    public function tokenString($value)
    {
        if (!empty($this->_args)) {
            $this->_syntaxError('string');
        }
        
        $this->_args[] = array(
            'type' => 'identifier', 
            'value' => $value
        );
    }
    
    public function tokenVariable($value)
    {
        if (!empty($this->_args)) {
            $this->_syntaxError('variable');
        }
        
        $this->_args[] = array(
            'type' => 'variable', 
            'value' => substr($value, 1)
        );
    }
    
    public function tokenComma()
    {
        $this->exitContext(array_merge($this->_args, $this->enterContext('Arguments')));
    }
    
    public function tokenParenthClose()
    {
        $this->exitContext($this->_args);
    }
}
