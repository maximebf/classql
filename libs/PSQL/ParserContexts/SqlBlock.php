<?php

namespace PSQL\ParserContexts;

use \PSQL\CatchAllContext;

class SqlBlock extends CatchAllContext
{
    protected $_curlyCount = 1;
    
    public function tokenCurlyOpen()
    {
        $this->_curlyCount++;
        $this->_value .= '{';
    }
    
    public function tokenCurlyClose()
    {
        if ($this->_curlyCount-- >= 1) {
            $this->_value .= '}';
            return;
        }
        
        $this->exitContext($this->_value);
    }
}
