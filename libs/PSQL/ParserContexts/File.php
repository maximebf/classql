<?php

namespace PSQL\ParserContexts;

use \PSQL\Context;

class File extends Context
{
    protected $_namespace;
    
    protected $_uses = array();
    
    protected $_objects = array();
    
    public function tokenNamespace()
    {
        $this->_namespace = trim($this->enterContext('Line'));
    }
    
    public function tokenUse()
    {
        $this->_uses = array_merge($this->_uses, $this->enterContext('UseDeclaration'));
    }
    
    public function tokenString($value)
    {
        $object = $this->enterContext('Prototype');
        $object['name'] = $value;
        $object['modifiers'] = $this->_latestModifiers;
        
        $this->_objects[$value] = $object;
        $this->_resetModifiers();
    }
    
    public function tokenEos()
    {
        $this->exitContext(array(
            'namespace' => $this->_namespace,
            'uses' => $this->_uses,
            'objects' => $this->_objects
        ));
    }
}
