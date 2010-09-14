<?php

namespace PSQL;

use ParseInContext\Context as BaseContext,
    PSQL\ParserException;

class CatchAllContext extends BaseContext
{
    protected $_value = '';
    
    public function __call($method, $args)
    {
        $this->_value .= $args[0];
    }
    
    protected function _syntaxError($token)
    {
        throw new ParserException("Syntax error, unexpected token '$token' in context '" . get_class($this) . "'");
    }
}
