<?php

namespace PSQL\ParserContexts;

use PSQL\Context,
    PSQL\ParserException;

class Prototype extends Context
{
    protected $_proto = array();
    
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
        if (array_key_exists($key, $this->_proto)) {
            if (!is_array($this->_proto[$key])) {
                $this->_proto[$key] = array($this->_proto[$key]);
            }
            $this->_proto[$key][] = $value;
        } else {
            $this->_proto[$key] = $value;
        }
    }
    
    public function tokenParenthOpen()
    {
        if (!empty($this->_proto)) {
            throw new ParserException('Wrong prototype declaration for function');
        }
        
        $params = $this->enterContext('Parameters');
        $func = array_merge($this->_proto, $this->enterContext('Operation'));
        $func['type'] = 'function';
        $func['params'] = $params;
        $this->exitContext($func);
    }
    
    public function tokenCurlyOpen()
    {
        $model = array_merge($this->_proto, $this->enterContext('Model'));
        $model['type'] = 'model';
        $this->exitContext($model);
    }
}
