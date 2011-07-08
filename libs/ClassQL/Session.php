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
    ClassQL\Generator\Generator;

/**
 * Central class to manage ClassQL's components
 */
final class Session
{
    /** @var Connection */
    private static $_connection;
    
    /** @var Cache */
    private static $_cache;
    
    /** @var Parser */
    private static $_parser;
    
    /** @var Generator */
    private static $_generator;
    
    /**
     * Setups and starts the session
     * 
     * Possible options:
     *  dsn: dsn for Connection (will create a Connection instance)
     *  username: username for Connection
     *  password: password for Connection
     *  connection: a Connection instance (won't use dsn, username and password)
     *  cache: a Cache instance
     *  parser: a Parser instance
     *  generator: a Generator instance
     *  profiler: a Profiler instance
     *  streamcache: the path where to cache generated files (false to disabled)
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
            'connection' => null,
            'cache' => null,
            'parser' => null,
            'generator' => null,
            'profiler' => null,
            'streamcache' => false,
            'streamcache_timestamp' => true
        ), $options);
        
        if ($options['connection'] === null && $options['dsn'] !== null) {
            $options['connection'] = new Connection(
                $options['dsn'], $options['username'], $options['password']);
        }
        
        if ($options['streamcache'] !== false) {
            StreamCache::setEnabled();
            StreamCache::setDirectory($options['streamcache']);
            StreamCache::setCheckTimestamp($options['streamcache_timestamp']);
        }
        StreamWrapper::register();
        
        self::$_connection = $options['connection'];
        self::$_cache = $options['cache'];
        self::$_parser = $options['parser'] ?: new Parser();
        self::$_generator = $options['generator'] ?: new \ClassQL\Generator\PHPGenerator();
        
        if ($options['profiler'] !== null) {
            self::$_connection->setProfiler($options['profiler']);
        }
    }
    
    /**
     * @return Connection
     */
    public static function getConnection()
    {
        return self::$_connection;
    }
    
    /**
     * @return Cache
     */
    public static function getCache()
    {
        return self::$_cache;
    }
    
    /**
     * @return Parser
     */
    public static function getParser()
    {
        return self::$_parser;
    }
    
    /**
     * @return Generator
     */
    public static function getGenerator()
    {
        return self::$_generator;
    }
    
    /**
     * @see Connection::beginTransaction()
     */
    public static function beginTransaction()
    {
        return self::$_connection->beginTransaction();
    }
    
    /**
     * @see Connection::commit()
     */
    public static function commitTransaction()
    {
        return self::$_connection->commit();
    }
    
    /**
     * @see Connection::rollBack()
     */
    public static function rollbackTransaction()
    {
        return self::$_connection->rollBack();
    }
    
    /**
     * @see Connection::transaction()
     */
    public static function transaction(Closure $closure)
    {
        return self::$_connection->transaction($closure);
    }
    
    /**
     * @see Connection::queryParams()
     */
    public static function query($query, array $params = array())
    {
        return self::$_connection->queryParams($query, $params);
    }
}

