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

class CLI
{
    private static $_commands = array(
        'schema' => '\ClassQL\CLI\Schema',
        'generate' => '\ClassQL\CLI\Generate'
    );
    
    public static function run(array $args = array())
    {
        $options = array();
        foreach ($args as $arg) {
            if (substr($arg, 0, 2) === '--') {
                $key = substr($arg, 2);
                $value = null;
                if (($sep = strpos($arg, '=')) !== false) {
                    $key = substr($arg, 0, $sep);
                    $value = substr($arg, $sep + 1);
                }
                $options[$key] = $value;
            }
        }
        $args = array_slice($args, count($options));
        
        if (!count($args)) {
            throw new Exception("Missing command name");
        }
        
        $command = array_shift($args);
        if (!isset(self::$_commands[$command])) {
            throw new Exception("Command '$command' does not exist");
        }
        
        $classname = self::$_commands[$command];
        $instance = new $classname();
        return $instance->execute($args, $options);
    }
    
    public function execute(array $args, array $options = array())
    {
        if (!count($args)) {
            throw new Exception("Not enough arguments");
        }
        
        $command = array_shift($args);
        $method = 'execute' . ucfirst($command);
        
        if (!method_exists($this, $method)) {
            throw new Exception("Command '$command' does not exist");
        }
        
        return call_user_func(array($this, $method), $args, $options);
    }
    
    public function println($message)
    {
        echo "$message\n";
    }
}