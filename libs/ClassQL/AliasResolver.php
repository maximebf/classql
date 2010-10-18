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
    private static $_aliases = array(
        'if' => '\ClassQL\Functions\Test::call',
        'switch' => '\ClassQL\Functions\SwitchArray::call'
    );
    
    /**
     * @param string $alias
     * @param mixed $value
     */
    public static function register($alias, $value)
    {
        self::$_aliases[$alias] = $value;
    }
    
    /**
     * @param string $alias
     * @param mixed $value
     */
    public static function unregister($alias)
    {
        if (isset(self::$_aliases[$alias])) {
            unset(self::$_aliases[$alias]);
        }
    }
    
    /**
     * Returns the value associated to an alias
     * 
     * @param string $alias
     * @return mixed
     */
    public static function resolve($alias)
    {
        if (isset(self::$_aliases[$alias])) {
            return self::$_aliases[$alias];
        }
        return null;
    }
}