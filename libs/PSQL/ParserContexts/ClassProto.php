<?php

namespace PSQL\ParserContexts;

use \PSQL\Context;

class ClassProto extends Context
{
    protected $_class = array();
    
    protected $_nextStringType;
    
    public function tokenAs()
    {
        $this->_nextStringType = 'table';
    }
    
    public function tokenExtends()
    {
        $this->_nextStringType = 'extends';
    }
    
    public function tokenString($value)
    {
        $this->_class[$this->_nextStringType] = $value;
    }
    
    public function tokenCurlyOpen()
    {
        $class = array_merge($this->_class, $this->enterContext('ClassBody'));
        $this->exitContext($class);
    }
}
