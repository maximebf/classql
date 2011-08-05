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
 * Creates and executes CLI commands
 */
class CLI
{
    /** @var array */
    private static $commands = array(
        'schema' => '\ClassQL\CLI\Schema',
        'generate' => '\ClassQL\CLI\Generate',
        'streamcache' => '\ClassQL\CLI\StreamCache'
    );
    
    /**
     * Parses the arguments then run the specified command
     * 
     * @param array $args
     */
    public static function run(array $argv = null)
    {
        $argv = $argv ?: array_slice($_SERVER['argv'], 1);
        $args = array();
        $options = array();
        foreach ($argv as $arg) {
            if (substr($arg, 0, 2) === '--') {
                $key = substr($arg, 2);
                $value = true;
                if (($sep = strpos($arg, '=')) !== false) {
                    $key = substr($arg, 2, $sep - 2);
                    $value = substr($arg, $sep + 1);
                }
                $options[$key] = $value;
            } else {
                $args[] = $arg;
            }
        }
        
        if (!count($args)) {
            throw new Exception("Missing command name");
        }
        
        $command = array_shift($args);
        if (!isset(self::$commands[$command])) {
            throw new Exception("Command '$command' does not exist");
        }
        
        $classname = self::$commands[$command];
        $instance = new $classname();
        return $instance->execute($args, $options);
    }
    
    /**
     * Registers a command
     * 
     * @param string $command
     * @param string $class
     */
    public static function register($command, $class)
    {
        self::$commands[$command] = $class;
    }
    
    /**
     * If not overriden, will execute the command specified
     * as the first argument
     * 
     * Commands must be defined as methods named after the
     * command, prefixed with execute (eg. create -> executeCreate)
     * 
     * @param array $args
     * @param array $options
     */
    public function execute(array $args, array $options = array())
    {
        if (!count($args)) {
            throw new Exception("Not enough arguments");
        }
        
        $command = str_replace(' ', '', ucwords(str_replace('-', ' ', array_shift($args))));
        $method = 'execute' . $command;
        
        if (!method_exists($this, $method)) {
            throw new Exception("Command '$command' does not exist");
        }
        
        return call_user_func(array($this, $method), $args, $options);
    }
    
    /**
     * Prints a line
     * 
     * @param string $message
     */
    public function println($message)
    {
        echo "$message\n";
    }
}