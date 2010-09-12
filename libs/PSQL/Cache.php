<?php

namespace PSQL;

abstract class Cache
{
    abstract public function has($filename);
    
    abstract public function get($filename);
    
    abstract public function set($filename, $content);
}
