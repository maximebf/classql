<?php

namespace PSQL\ParserContexts;

use \PSQL\Context;

class ClassBody extends Context
{
    protected $_columns = array();
    
    protected $_methods = array();
    
    protected $_nextName;
    
    public function tokenString($value)
    {
        if ($this->_nextName === null) {
            $this->_nextName = $value;
            return;
        }
        
        $column = $this->enterContext('Column');
        $column['name'] = $this->_nextName;
        $column['type'] = $value;
        
        $this->_columns[$this->_nextName] = $column;
        $this->_nextName = null;
    }
    
    public function tokenParenthOpen()
    {
        $params = $this->enterContext('Parameters');
        $method = $this->enterContext('Method');
        $method['name'] = $this->_nextName;
        $method['modifiers'] = $this->_latestModifiers;
        $method['params'] = $params;
        $method['attributes'] = $this->_latestAttributes;
        
        $this->_methods[$this->_nextName] = $method;
        $this->_nextName = null;
        $this->_resetLatests();
    }
    
    public function tokenCurlyClose()
    {
        $this->exitContext(array(
            'methods' => $this->_methods,
            'columns' => $this->_columns
        ));
    }
}
