<?php

namespace PSQL\ParserContexts;

use \PSQL\Context;

class UseDeclaration extends Context
{
    protected $_uses = array();
    
    public function tokenString($value)
    {
        $this->_uses[] = $value;
    }
    
    public function tokenComma()
    {
        $this->_uses = array_merge($this->_uses, $this->enterContext('UseDeclaration'));
        $this->exitContext($this->_uses);
    }
    
    public function tokenSemiColon()
    {
        $this->exitContext($this->_uses);
    }
}
