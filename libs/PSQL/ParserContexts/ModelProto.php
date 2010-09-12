<?php

namespace PSQL\ParserContexts;

use \PSQL\Context;

class ModelProto extends Context
{
    protected $_model = array();
    
    protected $_nextStringType;
    
    public function tokenAs()
    {
        $this->_nextStringType = 'table';
    }
    
    public function tokenExtends()
    {
        $this->_nextStringType = 'extends';
    }
    
    public function tokenImplements()
    {
        $this->_nextStringType = 'implements';
    }
    
    public function tokenString($value)
    {
        $key = $this->_nextStringType;
        if (array_key_exists($key, $this->_model)) {
            if (!is_array($this->_model[$key])) {
                $this->_model[$key] = array($this->_model[$key]);
            }
            $this->_model[$key][] = $value;
        } else {
            $this->_model[$key] = $value;
        }
    }
    
    public function tokenCurlyOpen()
    {
        $model = array_merge($this->_model, $this->enterContext('ModelBody'));
        $this->exitContext($model);
    }
    
    public function tokenEol()
    {
        $this->_syntaxError('Eol');
    }
}
