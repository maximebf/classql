<?php

abstract class AbstractStreamWrapper
{
	/** @var string */
	protected $_filename;
	
	/** @var string */
	protected $_content = '';
	
	/** @var int */
	protected $_position = 0;
	
	/**
	 * Registers the current stream wrapper with the specified protocol
	 *
	 * @param string $protocol
	 */
	public static function register($protocol)
	{
	    stream_wrapper_register($protocol, get_called_class());
	}
    
	/**
	 * Subclass should implement this method to open the file and fill
	 * the $content property
	 * 
	 * @param string $filename
	 * @param string $mode
	 * @param int $options
	 * @return bool
	 */
	abstract protected function openFile($filename, $mode, $options);
	
	/* --------------------------------------------------------------
	 * Belows is the code needed for stream wrappers
	 * -------------------------------------------------------------- */
	
    public function stream_open($path, $mode, $options, &$opened_path)
    {
    	$this->_filename = substr($path, 10);
    	$this->_position = 0;
    	
    	/* checks if the file can be included using include paths */
    	if (($options & STREAM_USE_PATH) == STREAM_USE_PATH) {
    		$includePaths = explode(PATH_SEPARATOR, get_include_path());
    		foreach ($includePaths as $incPath) {
    			$ds = DIRECTORY_SEPARATOR;
    			$pathFilename = rtrim($incPath, $ds) . $ds . ltrim($this->_filename, $ds);
    			if (file_exists($pathFilename)) {
    				$this->_filename = $pathFilename;
    			}
    		}
    		$opened_path = $this->_filename;
    	}
    	
    	return $this->openFile($this->_filename, $mode, $options);
    }

    public function stream_close()
    {
    }

    public function stream_read($count)
    {
        $ret = substr($this->_content, $this->_position, $count);
        $this->_position += strlen($ret);
        return $ret;
    }

    public function stream_write($data)
    {
       return 0;
    }

    public function stream_eof()
    {
        return $this->_position >= strlen($this->_content);
    }

    public function stream_tell()
    {
        return $this->_position;
    }

    public function stream_seek($offset, $whence)
    {
        switch($whence) {
            case SEEK_SET:
                if ($offset < strlen($this->_content) && $offset >= 0) {
                     $this->_position = $offset;
                     return true;
                } else {
                     return false;
                }
                break;
                
            case SEEK_CUR:
                if ($offset >= 0) {
                     $this->_position += $offset;
                     return true;
                } else {
                     return false;
                }
                break;
                
            case SEEK_END:
                if (strlen($this->_content) + $offset >= 0) {
                     $this->_position = strlen($this->_content) + $offset;
                     return true;
                } else {
                     return false;
                }
                break;
                
            default:
                return false;
        }
    }
    
    public function stream_stat()
    {
    	return stat($this->_filename);
    }
}
