<?php

namespace PSQL\ParserContexts;

use \PSQL\Context;

class Model extends Context
{
    protected $_latestFilters = array();
    
    protected $_columns = array();
    
    protected $_vars = array();
    
    protected $_methods = array();
    
    protected $_nextName;
    
    public function tokenFilter($value)
    {
        $this->_latestFilters[] = array(
            'name' => substr($value, 1),
            'args' => $this->enterContext('Filter')
        );
    }
    
    public function tokenString($value)
    {
        if ($this->_nextName === null) {
            $this->_nextName = $value;
            return;
        }
        
        $sql = $this->enterContext('Line');
        $column = array(
            'name' => $this->_nextName,
            'type' => $value,
            'sql' => trim($this->_nextName . ' ' . $value . $sql)
        );
        
        $this->_columns[] = $column;
        $this->_nextName = null;
    }
    
    public function tokenCurlyOpen()
    {
        if ($this->_nextName === null) {
            $this->_syntaxError('curlyOpen');
        }
        
        $this->_vars[] = array(
            'name' => $this->_nextName,
            'value' => $this->enterContext('Block')
        );
        $this->_nextName = null;
    }
    
    public function tokenParenthOpen()
    {
        if ($this->_nextName === null) {
            $this->_syntaxError('parenthOpen');
        }
        
        $params = $this->enterContext('Parameters');
        $method = $this->enterContext('Operation');
        $method['name'] = $this->_nextName;
        $method['modifiers'] = $this->_latestModifiers;
        $method['params'] = $params;
        $method['filters'] = $this->_latestFilters;
        
        $this->_methods[] = $method;
        $this->_nextName = null;
        $this->_latestFilters = array();
        $this->_resetModifiers();
    }
    
    public function tokenCurlyClose()
    {
        $this->exitContext(array(
            'methods' => $this->_methods,
            'columns' => $this->_columns,
            'vars' => $this->_vars
        ));
    }
}
