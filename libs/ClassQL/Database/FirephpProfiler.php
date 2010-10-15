<?php

namespace ClassQL\Database;

class FirephpProfiler implements Profiler
{
    /** @var array */
    protected $_currentQuery;
    
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
        
        if ($firephp = FirePHP::getInstance()) {
            $firephp->table('SQL', $table);
        }
    }
}