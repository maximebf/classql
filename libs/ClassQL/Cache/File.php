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
 * Memcache backend
 */
class File implements Cache
{
    /** @var string */
    protected $_directory;
    
    /**
     * @param string $dir
     */
    public function __construct($dir = '/tmp')
    {
        $this->setDirectory($dir);
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
        
        $this->_directory = rtrim($dir, DIRECTORY_SEPARATOR);
    }
    
    /**
     * @return string
     */
    public function getDirectory()
    {
        return $this->_directory;
    }
    
    /**
     * {@inheritDoc}
     */
    public function has($key)
    {
        return file_exists($this->getFilename($key));
    }
    
    /**
     * {@inheritDoc}
     */
    public function get($key)
    {
        return unserialize(file_get_contents($this->getFilename($key)));
    }
    
    /**
     * {@inheritDoc}
     */
    public function set($key, $content)
    {
        file_put_contents($this->getFilename($key), serialize($content));
    }
    
    /**
     * {@inheritDoc}
     */
    public function delete($key)
    {
        if ($this->has($key)) {
            unlink($this->getFilename($key));
        }
    }
    
    /**
     * Returns the filename associated to a key
     * 
     * @param string $key
     * @return string
     */
    public function getFilename($key)
    {
        return $this->_directory . DIRECTORY_SEPARATOR . $key;
    }
}
