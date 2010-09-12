<?php

namespace PSQL;

class NullCache extends Cache
{
    public function has($filename)
    {
        return false;
    }
    
    public function get($filename) 
    {
        return false;
    }
    
    public function set($filename, $content) 
    {
        return false;
    }
}
