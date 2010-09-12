<?php

namespace PSQL\ParserContexts;

use \PSQL\CatchAllContext;

class Query extends CatchAllContext
{
    protected $_vars = array();
    
    public function tokenVariable($value)
    {
        $this->_vars[] = substr($value, 1);
        $this->_value .= $value;
    }
    
    public function tokenCurlyClose()
    {
        $this->exitContext(array(
            'sql' => trim($this->_value),
            'vars' => $this->_vars
        ));
    }
}
