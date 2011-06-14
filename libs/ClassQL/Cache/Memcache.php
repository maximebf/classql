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
class Memcache implements Cache
{
    /** @var \Memcache */
    protected $_memcache;
    
    /**
     * @param \Memcache|string $memcache Memcache object or host name
     * @param int $port
     */
    public function __construct($memcache = null, $port = 11211)
    {
        if ($memcache instanceof \Memcache) {
            $this->_memcache = $memcache;
        } else if ($memcache !== null) {
            $this->_memcache = new \Memcache($memcache, $port);
        }
    }
    
    /**
     * @param \Memcache $memcache
     */
    public function setMemcache(\Memcache $memcache)
    {
        $this->_memcache = $memcache;
    }
    
    /**
     * @return \Memcache
     */
    public function getMemcache()
    {
        return $this->_memcache;
    }
    
    /**
     * {@inheritDoc}
     */
    public function has($key)
    {
        return $this->_memcache->get($key) !== false;
    }
    
    /**
     * {@inheritDoc}
     */
    public function get($key)
    {
        return $this->_memcache->get($key);
    }
    
    /**
     * {@inheritDoc}
     */
    public function set($key, $content, $ttl = null)
    {
        return $this->_memcache->set($key, $content, 0, $ttl);
    }
    
    /**
     * {@inheritDoc}
     */
    public function delete($key)
    {
        $this->_memcache->delete($key);
    }
}
