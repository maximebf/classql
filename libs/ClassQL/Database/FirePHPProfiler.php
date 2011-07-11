<?php

namespace ClassQL\Database;

if (!class_exists('FirePHP')) {
    throw new \Exception('ClassQL\Database\FirePHPProfiler needs the FirePHP class which is missing');
}

use FirePHP;

class FirePHPProfiler implements Profiler
{
    /** @var array */
    protected $_currentQuery;
    
    /**
     * {@inheritDoc}
     */
    public function log($message) 
    {
        if (!headers_sent() && $firephp = FirePHP::getInstance()) {
            $firephp->log($message);
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function startQuery($query, $params)
    {
        $this->_currentQuery = array($query, $params, microtime(true));
    }
    
    /**
     * {@inheritDoc}
     */
    public function stopQuery(\Exception $exception = null)
    {
        list($query, $params, $start) = $this->_currentQuery;
        $time = microtime(true) - $start;
        
        $query = implode(' ', array_map('trim', explode("\n", $query)));
        $outcome = 'SUCCESS';
        
        if ($exception !== null) {
            $outcome = 'ERROR: ' . $exception->getMessage();
        }
        
        $table = array(
            array('Query', 'Parameters', 'Time', 'Outcome'),
            array($query, $params, $time, $outcome)
        );
        
        if (!headers_sent() && $firephp = FirePHP::getInstance()) {
            $firephp->table($query, $table);
        }
    }
}