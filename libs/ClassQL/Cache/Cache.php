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
 * Interface for cache backends
 */
interface Cache
{
    /**
     * Checks if a key is already cached
     * 
     * @param string $key
     * @return bool
     */
    public function has($key);
    
    /**
     * Returns cached content
     * 
     * @param string $key
     * @return string
     */
    public function get($key);
    
    /**
     * Returns multiple cached content
     * 
     * @param array $keys
     */
    public function getMulti($keys);
    
    /**
     * Adds content to the cache
     * 
     * @param string $key
     * @param string $value
     * @param int $ttl
     */
    public function add($key, $value, $ttl = null);
    
    /**
     * Sets content in the cache
     * 
     * @param string $key
     * @param string $value
     * @param int $ttl
     */
    public function set($key, $value, $ttl = null);
    
    /**
     * Adds multiple content to the cache
     * 
     * @param array $items
     * @param int $ttl
     */
    public function setMulti($items, $ttl = null);
    
    /**
     * Deletes a cached key
     * 
     * @param string $key
     */
    public function delete($key);
}
