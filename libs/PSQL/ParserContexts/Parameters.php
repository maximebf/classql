<?php

namespace PSQL\ParserContexts;

use \PSQL\Context;

class Parameters extends Context
{
    protected $_vars = array();
    
    public function tokenVariable($value)
    {
        if (!empty($this->_args)) {
            $this->_syntaxError('variable');
        }
        
        $this->_vars[] = substr($value, 1);
    }
    
    public function tokenWildcard()
    {
        if (!empty($this->_args)) {
            $this->_syntaxError('wildcard');
        }
        
        $this->_vars[] = '*';
    }
    
    public function tokenComma()
    {
        $this->exitContext(array_merge($this->_vars, $this->enterContext('Parameters')));
    }
    
    public function tokenParenthClose()
    {
        $this->exitContext($this->_vars);
    }
}
