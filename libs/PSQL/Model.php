<?php

namespace PSQL;

abstract class Model
{
    protected $_connection;
    
    protected $_fields = array();
    
    public function __construct(array $data = array(), Connection $connection = null)
    {
        $this->_connection = $connection ?: Session::getConnection();
    }
    
    public function query($sql, $params = array())
    {
        
    }
}
