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

class FileCache extends Cache
{
    /** @var string */
    protected $_cacheDir;
    
    public function __construct($cacheDir = '/tmp')
    {
        $this->_cacheDir = $cacheDir;
    }
    
    public function setDirectory($dir)
    {
        $this->_cacheDir = $dir;
    }
    
    public function getDirectory()
    {
        return $this->_cacheDir;
    }
    
    public function has($filename)
    {
        return file_exists($this->getCacheName($filename));
    }
    
    public function get($filename)
    {
        if (!$this->has($filename)) {
            return false;
        }
        return file_get_contents($this->getCacheName($filename));
    }
    
    public function set($filename, $content)
    {
        file_put_contents($this->getCacheName($filename), $content);
    }
    
    public function getCacheName($filename)
    {
        return $this->_cacheDir . DIRECTORY_SEPARATOR . md5(realpath($filename));
    }
}
