<?php

namespace PSQL\ParserContexts;

use \PSQL\CatchAllContext;

class Column extends CatchAllContext
{
    public function tokenEol()
    {
        $this->exitContext(array());
    }
}
