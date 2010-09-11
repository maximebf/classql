<?php

namespace PSQL\ParserContexts;

use \PSQL\Context;

class Arguments extends Context
{
    protected $_args = array();
    
    public function tokenValue($value)
    {
        $this->_args[] = array(
            'type' => 'scalar', 
            'value' => str_replace('\\"', '"', trim($value, '"'))
        );
    }
    
    public function tokenString($value)
    {
        $this->_args[] = array(
            'type' => 'identifier', 
            'value' => $value
        );
    }
    
    public function tokenVariable($value)
    {
        $this->_args[] = array(
            'type' => 'variable', 
            'value' => substr($value, 1)
        );
    }
    
    public function tokenParenthClose()
    {
        $this->exitContext($this->_args);
    }
}
