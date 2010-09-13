<?php

namespace PSQL\ParserContexts;

use \PSQL\CatchAllContext;

class Comment extends CatchAllContext
{
    public function tokenEol()
    {
        $this->exitContext();
    }
}
