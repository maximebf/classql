<?php

namespace PSQL\ParserContexts;

use \PSQL\Context;

class Method extends Context
{
    public function tokenCurlyOpen()
    {
        $query = $this->enterContext('Query');
        $this->exitContext(array('query' => $query));
    }
    
    public function tokenPointer()
    {
        $callback = $this->enterContext('Callback');
        $this->exitContext(array('callback' => $callback));
    }
}
