<?php

namespace PSQL\ParserContexts;

use \PSQL\CatchAllContext;

class Line extends CatchAllContext
{
    public function tokenSemiColon()
    {
        $this->exitContext($this->_value);
    }
    
    public function tokenEol()
    {
        $this->exitContext($this->_value);
    }
}
