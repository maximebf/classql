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
class Memcached implements Cache
{
    /** @var \Memcached */
    protected $_memcached;
    
    /**
     * @param \Memcached|string $memcached Memcached object or host name
     * @param int $port
     */
    public function __construct($memcached = null, $port = 11211)
    {
        if ($memcached instanceof \Memcached) {
            $this->_memcached = $memcached;
        } else if ($memcache !== null) {
            $this->_memcached = new \Memcached();
            $this->_memcached->addServer($memcached, $port);
        }
    }
    
    /**
     * @param \Memcached $memcached
     */
    public function setMemcached(\Memcached $memcached)
    {
        $this->_memcached = $memcached;
    }
    
    /**
     * @return \Memcached
     */
    public function getMemcached()
    {
        return $this->_memcached;
    }
    
    /**
     * {@inheritDoc}
     */
    public function has($key)
    {
        return $this->_memcached->get($key) !== false;
    }
    
    /**
     * {@inheritDoc}
     */
    public function get($key)
    {
        return $this->_memcached->get($key);
    }

    /**
     * {@inheritDoc}
     */
    public function getMulti($keys) {
        $null = null;
        return $this->_memcached->getMulti($keys, $null, \Memcached::GET_PRESERVE_ORDER);
    }
    
    /**
     * {@inheritDoc}
     */
    public function set($key, $value, $ttl = null)
    {
        return $this->_memcached->set($key, $value, 0, $ttl);
    }

    /**
     * {@inheritDoc}
     */
    public function setMulti($items, $ttl = null) {
        $this->_memcached->setMulti($items, $ttl);
    }
    
    /**
     * {@inheritDoc}
     */
    public function delete($key)
    {
        $this->_memcached->delete($key);
    }
}
