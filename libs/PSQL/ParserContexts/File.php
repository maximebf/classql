<?php

namespace PSQL\ParserContexts;

use \PSQL\Context;

class File extends Context
{
    protected $_namespace;
    
    protected $_uses = array();
    
    protected $_models = array();
    
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
        $model = $this->enterContext('ModelProto');
        $model['name'] = $value;
        $model['attributes'] = $this->_latestAttributes;
        $model['modifiers'] = $this->_latestModifiers;
        
        $this->_models[$value] = $model;
        $this->_resetLatests();
    }
    
    public function tokenEos()
    {
        $this->exitContext(array(
            'namespace' => $this->_namespace,
            'uses' => $this->_uses,
            'models' => $this->_models
        ));
    }
}
