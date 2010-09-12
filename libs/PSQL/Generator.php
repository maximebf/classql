<?php

namespace PSQL;

class Generator
{
    protected $_template = 'PSQL/Template.php';
    
    protected $_namespace;
    
    public function generate($descriptor)
    {
        extract($descriptor);
        ob_start();
        include $this->_template;
        return ob_get_clean();
    }
}
