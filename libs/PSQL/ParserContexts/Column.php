<?php

namespace PSQL\ParserContexts;

use \ParseInContext\CatchAllContext;

class Column extends CatchAllContext
{
    public function tokenEol()
    {
        $this->exitContext(array());
    }
}
