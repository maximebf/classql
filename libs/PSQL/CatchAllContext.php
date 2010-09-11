<?php

namespace PSQL;

class CatchAllContext extends Context
{
    protected $_value = '';
    
    public function __call($method, $args)
    {
        $this->_value = trim($this->_value . ' ' . $args[0]);
    }
}
