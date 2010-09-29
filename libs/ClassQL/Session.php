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
    
    public static function start(array $options = array())
    {
        $options = array_merge(array(
            'dsn' => null,
            'username' => null,
            'password' => null,
            'connection' => null,
            'cache' => null,
            'parser' => null,
            'generator' => null
        ), $options);
        
        StreamWrapper::register();
        self::$_connection = $options['connection'] ?: 
            new Connection($options['dsn'], $options['username'], $options['password']);
        self::$_cache = $options['cache'] ?: new NullCache();
        self::$_parser = $options['parser'] ?: new Parser();
        self::$_generator = $options['generator'] ?: new \ClassQL\Generator\PHPGenerator();
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

