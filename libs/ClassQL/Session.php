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

use ClassQL\Parser\Parser,
    ClassQL\Database\Connection,
    ClassQL\Generator\Generator,
    ClassQL\Database\Profiler;

/**
 * Central class to manage ClassQL's components
 */
final class Session
{
    public static $defaultConnectionName = 'default';

    /** @var array of Connection */
    private static $connections = array();
    
    /** @var Cache */
    private static $cache;
    
    /** @var Parser */
    private static $parser;
    
    /** @var Generator */
    private static $generator;
    
    /** @var Profiler */
    private static $profiler;
    
    /**
     * Setups and starts the session
     * 
     * Possible options:
     *  dsn: dsn for Connection (will create a Connection instance)
     *  username: username for Connection
     *  password: password for Connection
     *  connection: a Connection instance (won't use dsn, username and password)
     *  connections: an associative array to initialize a pool of connections
     *  cache: a Cache instance
     *  parser: a Parser instance
     *  generator: a Generator instance
     *  profiler: a Profiler instance
     *  streamcache: the path where to cache generated files (false to disabled)
     *  streamcache_timestamp: whether to check for the compiled file timestamp
     *  
     * All keys are optional apart for either the connection or the dsn ones
     * 
     * @param array $options
     */
    public static function start(array $options = array())
    {
        $options = array_merge(array(
            'dsn' => null,
            'username' => null,
            'password' => null,
            'options' => array(),
            'connection' => null,
            'connections' => array(),
            'cache' => null,
            'parser' => null,
            'generator' => null,
            'profiler' => null,
            'streamcache' => false,
            'streamcache_timestamp' => true
        ), $options);

        if ($options['profiler'] !== null) {
            self::$profiler = $options['profiler'];
        }
        
        $connection = $options['connection'];
        if ($connection === null && $options['dsn'] !== null) {
            $connection = array(
                'dsn' => $options['dsn'], 
                'username' => $options['username'], 
                'password' => $options['password'],
                'options' => $options['options']
            );
        }
        if ($connection) {
            $options['connections'][self::$defaultConnectionName] = $connection;
        }

        foreach ($options['connections'] as $key => $value) {
            if ($options['profiler'] !== null) {
                if (is_string($value)) {
                    $value = array('dsn' => $value);
                }
                if (is_array($value)) {
                    $value['options'] = array_merge($value['options'] ?: array(), array(
                        Connection::CLASSQL_PROFILER => $options['profiler']
                    ));
                } else {
                    $value->setProfiler($options['profiler']);
                }
            }
            self::addConnection($key, $value);
        }
        
        if ($options['streamcache'] !== false) {
            StreamWrapper::setCacheOptions(array(
                'enabled' => true,
                'dir' => $options['streamcache'],
                'check_timestamp' => $options['streamcache_timestamp']
            ));
        }
        StreamWrapper::register();
        
        self::$cache = $options['cache'];
        self::$parser = $options['parser'] ?: new Parser();
        self::$generator = $options['generator'] ?: new \ClassQL\Generator\PHPGenerator();
    }

    /**
     * @param string $name
     * @param Connection|array $connection
     */
    public static function addConnection($name, $connection) 
    {
        if (is_string($connection)) {
            $connection = array('dsn' => $connection);
        }
        self::$connections[$name] = $connection;
    }
    
    /**
     * @param string $name
     * @return Connection
     */
    public static function getConnection($name = null)
    {
        $name = $name ?: self::$defaultConnectionName;
        if (!isset(self::$connections[$name])) {
            throw new Exception('No connections have been registered');
        }
        if (is_array(self::$connections[$name])) {
            self::$connections[$name] = new Connection(
                self::$connections[$name]['dsn'], 
                self::$connections[$name]['username'], 
                self::$connections[$name]['password'],
                self::$connections[$name]['options'] ?: array());
        }
        return self::$connections[$name];
    }

    /**
     * @return array
     */
    public static function getConnections()
    {
        $keys = array_keys(self::$connections);
        return array_combine($keys, array_map('\ClassQL\Session::getConnection', $keys));
    }
    
    /**
     * @return Cache
     */
    public static function getCache()
    {
        return self::$cache;
    }
    
    /**
     * @return Parser
     */
    public static function getParser()
    {
        return self::$parser;
    }
    
    /**
     * @return Generator
     */
    public static function getGenerator()
    {
        return self::$generator;
    }
    
    /**
     * @return Generator
     */
    public static function getProfiler()
    {
        return self::$profiler;
    }
}

