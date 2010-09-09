<?php

namespace PSQL\ParserContexts;

use \ParseInContext\Context;

class Klass extends Context
{
    protected $_latestAttributes = array();
    
    protected $_nextFunctionIsStatic = false;
    
    protected $_columns = array();
    
    protected $_functions = array();
    
    protected $_nextName;
    
    public function tokenAttribute($value)
    {
        $this->_latestAttributes[] = $value;
    }
    
    public function tokenStatic()
    {
        $this->_nextFunctionIsStatic = true;
    }
    
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
        $args = $this->enterContext('Arguments');
        $func = $this->enterContext('Method');
        $func['name'] = $this->_nextName;
        $func['static'] = $this->_nextFunctionIsStatic;
        $func['args'] = $args;
        
        $this->_functions[$this->_nextName] = $func;
        $this->_nextName = null;
        $this->_nextFunctionIsStatic = false;
    }
    
    public function tokenCurlyClose()
    {
        $this->exitContext(array(
            'functions' => $this->_functions,
            'columns' => $this->_columns
        ));
    }
}
