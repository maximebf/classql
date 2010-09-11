<?php

namespace PSQL\ParserContexts;

use \PSQL\Context;

class Parameters extends Context
{
    protected $_vars = array();
    
    public function tokenVariable($value)
    {
        $this->_vars[] = substr($value, 1);
    }
    
    public function tokenWildcard()
    {
        $this->_vars[] = '*';
    }
    
    public function tokenParenthClose()
    {
        $this->exitContext($this->_vars);
    }
}
