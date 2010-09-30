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
 
namespace ClassQL\Cache;

/**
 * File backend
 */
class File implements Cache
{
    /** @var string */
    protected $_cacheDir;
    
    /** @var bool */
    protected $_checkTimestamp;
    
    /**
     * @param string $cacheDir
     * @param bool $checkTimestamp
     */
    public function __construct($cacheDir = '/tmp', $checkTimestamp = true)
    {
        $this->setDirectory($cacheDir);
        $this->setCheckTimestamp($checkTimestamp);
    }
    
    /**
     * @param string $dir
     */
    public function setDirectory($dir)
    {
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        } else if (!is_dir($dir)) {
            throw new \ClassQL\Exception("Path '$dir' must be a directory");
        }
        
        $this->_cacheDir = rtrim($dir, DIRECTORY_SEPARATOR);
    }
    
    /**
     * @return string
     */
    public function getDirectory()
    {
        return $this->_cacheDir;
    }
    
    /**
     * @param bool $checkTimestamp
     */
    public function setCheckTimestamp($checkTimestamp = true)
    {
        $this->_checkTimestamp = $checkTimestamp;
    }
    
    /**
     * @return bool
     */
    public function isTimestampChecked()
    {
        return $this->_checkTimestamp;
    }
    
    /**
     * {@inheritDoc}
     */
    public function has($filename)
    {
        if (!file_exists($this->getCacheName($filename))) {
            return false;
        }
        return !$this->_checkTimestamp || 
               filemtime($this->getCacheName($filename)) >= filemtime($filename);
    }
    
    /**
     * {@inheritDoc}
     */
    public function get($filename)
    {
        if (!$this->has($filename)) {
            return false;
        }
        return file_get_contents($this->getCacheName($filename));
    }
    
    /**
     * {@inheritDoc}
     */
    public function set($filename, $content)
    {
        file_put_contents($this->getCacheName($filename), $content);
    }
    
    /**
     * Returns the filename of the cached file
     * 
     * @param string $filename
     * @return string
     */
    public function getCacheName($filename)
    {
        return $this->_cacheDir . DIRECTORY_SEPARATOR . md5(realpath($filename)) . '.classql.php';
    }
}
