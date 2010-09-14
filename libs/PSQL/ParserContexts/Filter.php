<?php

namespace PSQL\ParserContexts;

use \PSQL\Context;

class Filter extends Context
{
    protected $_args = array();
    
    public function tokenParenthOpen()
    {
        $this->_args = $this->enterContext('Arguments');
    }
    
    public function tokenEol()
    {
        $this->exitContext($this->_args);
    }
}
