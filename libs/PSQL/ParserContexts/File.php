<?php

namespace PSQL\ParserContexts;

use \PSQL\Context;

class File extends Context
{
    protected $_namespace;
    
    protected $_models = array();
    
    public function tokenNamespace()
    {
        $this->_namespace = trim($this->enterContext('Line'));
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
            'models' => $this->_models
        ));
    }
}
