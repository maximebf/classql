<?php

namespace PSQL;

use AbstractStreamWrapper;

class StreamWrapper extends AbstractStreamWrapper
{
	protected function openFile($filename, $mode, $options)
	{
	    $cache = Session::getCache();
	    
	    if (($this->_content = $cache->get($filename)) === false) {
	        $parser = Session::getParser();
	        $generator = Session::getGenerator();
	        $descriptor = $parser->parseFile($filename);
	        $this->_content = $generator->generate($descriptor);
	        $cache->set($filename, $this->_content);
	    }
	}
}
