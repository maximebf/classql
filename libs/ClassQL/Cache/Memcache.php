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
    protected $memcache;
    
    /**
     * @param \Memcache|string $memcache Memcache object or host name
     * @param int $port
     */
    public function __construct($memcache = null, $port = 11211)
    {
        if ($memcache instanceof \Memcache) {
            $this->memcache = $memcache;
        } else if ($memcache !== null) {
            $this->memcache = new \Memcache($memcache, $port);
        }
    }
    
    /**
     * @param \Memcache $memcache
     */
    public function setMemcache(\Memcache $memcache)
    {
        $this->memcache = $memcache;
    }
    
    /**
     * @return \Memcache
     */
    public function getMemcache()
    {
        return $this->memcache;
    }
    
    /**
     * {@inheritDoc}
     */
    public function has($key)
    {
        return $this->memcache->get($key) !== false;
    }
    
    /**
     * {@inheritDoc}
     */
    public function get($key)
    {
        return $this->memcache->get($key);
    }
    
    /**
     * {@inheritDoc}
     */
    public function getMulti($keys)
    {
        return array_map(array($this->memcache, 'get'), $keys);
    }
    
    /**
     * {@inheritDoc}
     */
    public function add($key, $value, $ttl = null)
    {
        return $this->memcache->add($key, $value, 0, $ttl);
    }
    
    /**
     * {@inheritDoc}
     */
    public function set($key, $value, $ttl = null)
    {
        return $this->memcache->set($key, $value, 0, $ttl);
    }
    
    /**
     * {@inheritDoc}
     */
    public function setMulti($items, $ttl = null)
    {
        foreach ($items as $key => $value) {
            $this->memcache->set($key, $value, 0, $ttl);
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function delete($key)
    {
        $this->memcache->delete($key);
    }
}
