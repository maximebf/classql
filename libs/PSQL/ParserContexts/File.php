<?php

namespace PSQL\ParserContexts;

use \PSQL\Context;

class File extends Context
{
    protected $_nextClassName = null;
    
    protected $_classes = array();
    
    public function tokenString($value)
    {
        $class = $this->enterContext('ClassProto');
        $class['name'] = $value;
        $class['attributes'] = $this->_latestAttributes;
        $class['modifiers'] = $this->_latestModifiers;
        
        $this->_classes[$value] = $class;
        $this->_resetLatests();
    }
    
    public function tokenEos()
    {
        $this->exitContext($this->_classes);
    }
}
