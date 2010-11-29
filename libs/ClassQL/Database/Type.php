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
 
namespace ClassQL\Database;

use ClassQL\AliasResolver;

/**
 * 
 */
abstract class Type
{
    private static $_registeredTypes = array(
        'bool' => '\ClassQL\Database\Types\Bool',
        'pgbool' => '\ClassQL\Database\Types\PgBool',
        'pgarray' => '\ClassQL\Database\Types\PgArray',
        'serialized' => '\ClassQL\Database\Types\Serialized'
    );
    
    public static final function registerType($className = null, $alias = null)
    {
        if (get_called_class() !== __CLASS__) {
            $alias = $className;
            $className = get_called_class();
        }
        $alias = $alias ?: substr($className, strrpos($className, '\\') + 1);
        self::$_registeredTypes[$alias] = $className;
    }
    
    public static final function hasType($alias)
    {
        return isset(self::$_registeredTypes[$alias]);
    }
    
    public static final function getTypeClassName($alias)
    {
        if (self::hasType($alias)) {
            return self::$_registeredTypes[$alias];
        }
    }
    
    public static final function unregisterType($className = null)
    {
        if (get_called_class() !== __CLASS__) {
            $className = get_called_class();
        }
        if ($key = array_search($className, self::$_registeredTypes)) {
            unset(self::$_registeredTypes[$key]);
        }
    }
    
    public static function filterInput($value, $source)
    {
        return $value;
    }
    
    public static function filterOutput($value, $source)
    {
        return $value;
    }
    
    public static abstract function getSqlTypeDef();
}