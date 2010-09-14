<?php

namespace PSQL\ParserContexts;

class Query extends Block
{
    protected $_vars = array();
    
    public function tokenVariable($value)
    {
        $this->_vars[] = substr($value, 1);
        $this->_value .= $value;
    }
    
    public function tokenCurlyClose()
    {
        if ($this->_curlyCount-- > 1) {
            $this->_value .= '}';
            return;
        }
        
        $this->exitContext(array(
            'sql' => trim($this->_value),
            'vars' => $this->_vars
        ));
    }
}
