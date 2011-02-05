<?php
/**
 * ClassQL
 * Copyright (c) 2010 Maxime Bouroumeau-Fuseau
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author Maxime Bouroumeau-Fuseau
 * @copyright 2010 (c) Maxime Bouroumeau-Fuseau
 * @license http://www.opensource.org/licenses/mit-license.php
 * @link http://github.com/maximebf/classql
 */
 
namespace ClassQL;

/**
 * Allows to loads models using the classql:// protocol
 */
class StreamWrapper
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
    public static function register()
    {
        stream_wrapper_register('classql', 'ClassQL\StreamWrapper');
    }
    
    /**
     * @param string $filename
     * @param string $mode
     * @param int $options
     * @return bool
     */
    protected function openFile($filename, $mode, $options)
    {
        if (!file_exists($filename)) {
            return false;
        }
        
        if (StreamCache::isEnabled() && ($this->_content = StreamCache::get($filename)) !== false) {
            return true;
        }
        
        $parser = Session::getParser();
        $generator = Session::getGenerator();
        $ast = $parser->parseFile($filename);
        $this->_content = $generator->generate($ast);
        
        if (StreamCache::isEnabled()) {
            StreamCache::set($filename, $this->_content);
        }
        
        return true;
    }
    
    /* --------------------------------------------------------------
     * Belows is the code needed for stream wrappers
     * -------------------------------------------------------------- */
    
    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $this->_filename = substr($path, strpos($path, '/') + 2);
        $this->_position = 0;
        
        // checks if the file can be included using include paths
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
    
    public function url_stat()
    {
        return stat($this->_filename);
    }
}