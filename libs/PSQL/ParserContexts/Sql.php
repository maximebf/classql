<?php

namespace PSQL\ParserContexts;

use \ParseInContext\CatchAllContext;

class Sql extends CatchAllContext
{
    public function tokenCurlyClose()
    {
        $this->exitContext($this->_value);
    }
}
