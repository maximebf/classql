<?php

namespace PSQL\ParserContexts;

use \PSQL\Context;

class Model extends Context
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
        
        $sql = $this->enterContext('Line');
        $column = array(
            'name' => $this->_nextName,
            'type' => $value,
            'sql' => trim($this->_nextName . ' ' . $value . $sql)
        );
        
        $this->_columns[$this->_nextName] = $column;
        $this->_nextName = null;
    }
    
    public function tokenCurlyOpen()
    {
        if ($this->_nextName === null) {
            $this->_syntaxError('curlyOpen');
        }
        
        $column = array(
            'name' => $this->_nextName,
            'type' => 'composite',
            'value' => $this->enterContext('SqlBlock')
        );
        
        $this->_columns[$this->_nextName] = $column;
        $this->_nextName = null;
    }
    
    public function tokenParenthOpen()
    {
        $params = $this->enterContext('Parameters');
        $method = $this->enterContext('Operation');
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
