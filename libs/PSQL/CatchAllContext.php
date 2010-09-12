<?php

namespace PSQL;

class CatchAllContext extends Context
{
    protected $_value = '';
    
    public function tokenEol()
    {
        $this->_value .= "\n";
    }
    
    public function tokenWhitespace()
    {
        $this->_value .= ' ';
    }
    
    public function __call($method, $args)
    {
        $this->_value .= $args[0];
    }
}
