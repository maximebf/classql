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

/**
 * Loads classes and models
 */
class Loader
{
    /**
     * Registers and autoloader for the given namespace
     * 
     * Classes will be searched in the $dir folder. If $parse
     * is set to true, the classql:// proto will be used.
     * 
     * @param string $namespace
     * @param string $dir
     * @param bool $parse
     */
    public static function register($namespace, $dir, $parse = false)
    {
        $namespade = trim($namespace, '\\');
        spl_autoload_register(function($classname) use ($namespace, $dir, $parse) {
            $classname = ltrim($classname, '\\');
            if (substr($classname, 0, strlen($namespace)) !== $namespace) {
                return false;
            }
            
            $filename = $dir . str_replace('\\', DIRECTORY_SEPARATOR, substr($classname, 
                        strlen($namespace))) . ($parse ? '.cql' : '.php');
                      
            if ($parse) {
                $filename = 'classql://' . $filename;
            }
            
            require_once $filename;
        });
    }
}
