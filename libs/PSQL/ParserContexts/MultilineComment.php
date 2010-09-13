<?php

namespace PSQL\ParserContexts;

use \PSQL\CatchAllContext;

class MultilineComment extends CatchAllContext
{
    public function tokenCommentClose()
    {
        $this->exitContext();
    }
}
