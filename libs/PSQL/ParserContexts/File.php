<?php

namespace PSQL\ParserContexts;

use \ParseInContext\Context;

class File extends Context
{
    protected $_latestAttributes = array();
    
    protected $_nextClassName = null;
    
    protected $_classes = array();
    
    public function tokenAttribute($value)
    {
        $this->_latestAttributes[] = $value;
    }
    
    public function tokenString($value)
    {
        if ($this->_nextClassName === null) {
            $this->_nextClassName = $value;
        }
    }
    
    public function tokenCurlyOpen()
    {
        $class = $this->enterContext('Klass');
        $class['name'] = $this->_nextClassName;
        $class['attributes'] = $this->_latestAttributes;
        
        $this->_latestAttributes = array();
        $this->_classes[$this->_nextClassName] = $class;
        $this->_nextClassName = null;
    }
    
    public function tokenEos()
    {
        $this->exitContext($this->_classes);
    }
}
