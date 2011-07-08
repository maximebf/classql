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
 * Caches generated files
 */
class StreamCache
{
    /** @var bool */
    static private $_enabled = false;
    
    /** @var string */
    static private $_cacheDir = '/tmp';
    
    /** @var bool */
    static private $_checkTimestamp = true;
    
    /**
     * @param bool $enabled
     */
    public static function setEnabled($enabled = true)
    {
        self::$_enabled = $enabled;
    }
    
    /**
     * @return bool
     */
    public static function isEnabled()
    {
        return self::$_enabled;
    }
    
    /**
     * @param string $dir
     */
    public static function setDirectory($dir)
    {
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        } else if (!is_dir($dir)) {
            throw new Exception("Path '$dir' must be a directory");
        }
        
        self::$_cacheDir = rtrim($dir, DIRECTORY_SEPARATOR);
    }
    
    /**
     * @return string
     */
    public static function getDirectory()
    {
        return self::$_cacheDir;
    }
    
    /**
     * @param bool $checkTimestamp
     */
    public static function setCheckTimestamp($checkTimestamp = true)
    {
        self::$_checkTimestamp = $checkTimestamp;
    }
    
    /**
     * @return bool
     */
    public static function isTimestampChecked()
    {
        return self::$_checkTimestamp;
    }
    
    /**
     * Checks if a file is cached
     * 
     * @return bool
     */
    public static function has($filename)
    {
        if (!file_exists($cachedName = self::getCacheName($filename))) {
            return false;
        }
        if (self::$_checkTimestamp) {
            return filemtime($cachedName) >= filemtime($filename);
        }
        return true;
    }
    
    /**
     * Returns the content of a cached file, false otherwise
     * 
     * @return string
     */
    public static function get($filename)
    {
        if (!self::has($filename)) {
            return false;
        }
        return file_get_contents(self::getCacheName($filename));
    }
    
    /**
     * Caches some content under a filename
     * 
     * @param string $filename
     * @param string $content
     */
    public static function set($filename, $content)
    {
        file_put_contents(self::getCacheName($filename), $content);
    }
    
    /**
     * Clears the cache
     */
    public static function clear()
    {
        foreach (new \DirectoryIterator(self::$_cacheDir) as $file) {
            if (!$file->isFile() || substr($file->getFilename(), 0, 1) === '.') {
                continue;
            }
            unlink($file->getPathname());
        }
    }
    
    /**
     * Returns the filename of a cached file
     * 
     * @param string $filename
     * @return string
     */
    public static function getCacheName($filename)
    {
        return self::$_cacheDir . DIRECTORY_SEPARATOR . md5(realpath($filename)) . '.classql.php';
    }
}
