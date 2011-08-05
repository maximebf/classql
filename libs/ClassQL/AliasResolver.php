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

class AliasResolver
{
    /** @var array */
    protected static $aliases = array();
    
    /**
     * @param string $alias
     * @param mixed $value
     */
    public static function registerAlias($alias, $value = null)
    {
        $classname = get_called_class();
        if (!isset(self::$aliases[$classname])) {
            self::$aliases[$classname] = array();
        }

        if (is_array($alias)) {
            foreach ($alias as $k => $v) {
                self::$aliases[$classname][$k] = $v;
            }
        } else {
            self::$aliases[$classname][$alias] = $value;
        }
    }
    
    /**
     * @param string $alias
     * @param mixed $value
     */
    public static function unregisterAlias($alias)
    {
        $classname = get_called_class();
        if (isset(self::$aliases[$classname]) && 
            isset(self::$aliases[$classname][$alias])) {
                unset(self::$aliases[$classname][$alias]);
        }
    }
    
    /**
     * Returns the value associated to an alias
     * 
     * @param string $alias
     * @return mixed
     */
    public static function resolveAlias($alias)
    {
        $classname = get_called_class();
        if (isset(self::$aliases[$classname]) && 
            isset(self::$aliases[$classname][$alias])) {
                return $classname::$aliases[$alias];
        }
        return null;
    }
}