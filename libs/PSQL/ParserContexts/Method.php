<?php

namespace PSQL\ParserContexts;

use \ParseInContext\Context;

class Method extends Context
{
    public function tokenCurlyOpen()
    {
        $sql = $this->enterContext('Sql');
        $this->exitContext(array('sql' => $sql));
    }
    
    public function tokenPointer()
    {
        $callback = $this->enterContext('Callback');
        $this->exitContext(array('callback' => $callback));
    }
}
