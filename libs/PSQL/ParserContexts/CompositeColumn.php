<?php

namespace PSQL\ParserContexts;

use \PSQL\CatchAllContext;

class CompositeColumn extends CatchAllContext
{
    public function tokenCurlyClose()
    {
        $this->exitContext($this->_value);
    }
}
