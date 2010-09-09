<?php

namespace PSQL\ParserContexts;

namespace PSQL\ParserContexts;

use \ParseInContext\Context;

class Arguments extends Context
{
    protected $_vars = array('wildcard' => false);
    
    public function tokenVariable($value)
    {
        $this->_vars[] = $value;
    }
    
    public function tokenWildcard()
    {
        $this->_vars['wildcard'] = true;
    }
    
    public function tokenParenthClose()
    {
        $this->exitContext($this->_vars);
    }
}
