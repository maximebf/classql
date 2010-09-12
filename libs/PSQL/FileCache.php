<?php

namespace PSQL;

class FileCache extends Cache
{
    /** @var string */
    protected $_cacheDir;
    
    public function __construct($cacheDir = '/tmp')
    {
        $this->_cacheDir = $cacheDir;
    }
    
    public function setDirectory($dir)
    {
        $this->_cacheDir = $dir;
    }
    
    public function getDirectory()
    {
        return $this->_cacheDir;
    }
    
    public function has($filename)
    {
        return file_exists($this->getCacheName($filename));
    }
    
    public function get($filename)
    {
        if (!$this->has($filename)) {
            return false;
        }
        return file_get_contents($this->getCacheName($filename));
    }
    
    public function set($filename, $content)
    {
        file_put_contents($this->getCacheName($filename), $content);
    }
    
    public function getCacheName($filename)
    {
        return $this->_cacheDir . DIRECTORY_SEPARATOR . md5(realpath($filename));
    }
}
