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

/**
 * Log queries to a file
 */
class FileProfiler implements Profiler
{
    /** @var string */
    protected $_filename;
    
    /** @var int */
    protected $_start;
    
    /** @var int */
    protected $_nbQueries = 0;
    
    /**
     * @param string $filename The file where queries will be logged
     */
    public function __construct($filename)
    {
        $this->_filename = $filename;
        $today = date('Y-m-d H:i:s');
        $this->_write('-------------------------------');
        $this->_write("BEGIN $today");
    }
    
    public function __destruct()
    {
        $this->_write("END ($this->_nbQueries queries executed)");
    }
    
    /**
     * {@inheritDoc}
     */
    public function startQuery($query, $params)
    {
        $this->_start = microtime(true);
        $params = implode(' ', array_map('trim', explode("\n", var_export($params, true))));
        $query = implode(' ', array_map('trim', explode("\n", $query)));
        $this->_write("START:   $query\n         $params");
        $this->_nbQueries++;
    }
    
    /**
     * {@inheritDoc}
     */
    public function stopQuery(\Exception $exception = null)
    {
        $time = microtime(true) - $this->_start;
        if ($exception !== null) {
            $this->_write("ERROR:   $time " . $exception->getMessage());
        } else {
            $this->_write("SUCCESS: $time");
        }
    }
    
    /**
     * Writes a message to the log file
     * 
     * @param string $message
     */
    protected function _write($message)
    {
        $fp = fopen($this->_filename, 'a');
        fwrite($fp, "$message\n");
        fclose($fp);
    }
}