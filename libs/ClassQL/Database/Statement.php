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

use \PDOStatement, 
    \PDOException;

/**
 * Custom statement that adds profiling capabilities
 */
class Statement extends PDOStatement
{
    /** @var Profiler */
    protected $_profiler;
    
    /**
     * @param Profiler $profiler
     */
    protected function __construct(Profiler $profiler = null) 
    {
        $this->_profiler = $profiler;
    }
    
    /**
     * {@inheritDoc}
     */
    public function execute($params = array())
    {
        $this->_profiler !== null && $this->_profiler->startQuery($this->queryString, $params);
    
        try {
            $success = parent::execute($params);
        } catch (PDOException $e) {
            $this->_profiler !== null && $this->_profiler->stopQuery($e);
            throw $e;
        }
    
        $this->_profiler !== null && $this->_profiler->stopQuery();
        
        if ($success) {
            return $this;
        }
        return false;
    }
}
