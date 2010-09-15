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

final class Session
{
    private static $_connection;
    
    private static $_cache;
    
    private static $_parser;
    
    private static $_generator;
    
    public static function registerAutoloader()
    {
	    spl_autoload_register(function($className) {
	        $filename = str_replace('\\', DIRECTORY_SEPARATOR, trim($className, '\\')) . '.php';
	        require_once $filename;
	    });
    }
    
    public static function registerStreamWrapper()
    {
        StreamWrapper::register('psql');
    }
    
    public static function configure(Connection $connection, Cache $cache = null, 
        Parser $parser = null, Generator $generator = null)
    {
        self::$_connection = $connection;
        self::$_cache = $cache ?: new NullCache();
        self::$_parser = $parser ?: new Parser();
        self::$_generator = $generator ?: new Generator();
    }
    
    public static function getConnection()
    {
        return self::$_connection;
    }
    
    public static function getCache()
    {
        return self::$_cache;
    }
    
    public static function getParser()
    {
        return self::$_parser;
    }
    
    public static function getGenerator()
    {
        return self::$_generator;
    }
}

