<?php

namespace PSQL\ParserContexts;

use \PSQL\Context;

class Callback extends Context
{
    protected $_callback;
    
    public function tokenCallback($value)
    {
        $this->_callback = $value;
    }
    
    public function tokenParenthOpen()
    {
        $args = $this->enterContext('Arguments');
        
        $this->exitContext(array(
            'name' => $this->_callback,
            'args' => $args
        ));
    }
}
