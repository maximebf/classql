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

use \PDO,
    \PDOException,
    \ClassQL\SqlString,
    \ClassQL\Session,
    \ClassQL\Cache\Cache;

/**
 * Thin wrapper around PDO
 */
class Connection extends PDO
{
    const FETCH_COMPOSITE = 20;
    
    const FETCH_TYPED = 200000;
    
    /** @var bool */
    protected $_nestedTransactions = true;
    
    /** @var int */
    protected $_transactionCount = 0;
    
    /** @var Profiler */
    protected $_profiler;
    
    /** @var Cache */
    protected $_cache;
    
    /**
     * {@inheritDoc}
     */
    public function __construct($dsn, $username = null, $passwd = null, array $options = array())
    {
        parent::__construct($dsn, $username, $passwd, $options);
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, 
            array('ClassQL\Database\Statement', array()));
    }
    
    /**
     * @param Profiler $profiler
     */
    public function setProfiler(Profiler $profiler = null)
    {
        $this->_profiler = $profiler;
        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, 
            array('ClassQL\Database\Statement', array($profiler)));
    }
    
    /**
     * @return Profiler
     */
    public function getProfiler()
    {
        return $this->_profiler;
    }
    
    /**
     * Enables or disable nested transactions
     * 
     * @param bool $enable
     */
    public function enableNestedTransactions($enable = true)
    {
        $this->_nestedTransactions = $enable;
    }
    
    /**
     * Checks if nested transactions are enabled
     * 
     * @return bool
     */
    public function areNestedTransactionEnabled()
    {
        return $this->_nestedTransactions;
    }
    
    /**
     * {@inheritDoc}
     */
    public function beginTransaction()
    {
        if (!$this->_nestedTransactions || $this->_transactionCount++ === 0) {
            parent::beginTransaction();
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function commit()
    {
        if (!$this->_nestedTransactions || $this->_transactionCount-- === 1) {
            parent::commit();
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function rollBack()
    {
        if (!$this->_nestedTransactions || $this->_transactionCount-- === 1) {
            parent::rollBack();
        }
    }
    
    /**
     * Wraps a closure into a transaction
     * 
     * @param Closure $closure
     * @return mixed
     */
    public function transaction(\Closure $closure)
    {
        $this->beginTransaction();
        try {
            $result = $closure($this);
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
        $this->commit();
        return $result;
    }
    
    /**
     * {@inheritDoc}
     */
    public function query($statement)
    {
        return $this->_profileQuery('parent::query', $statement);
    }
    
    /**
     * Prepares and executes a statement
     * 
     * @param string $query
     * @param array $params
     * @return Statement
     */
    public function queryParams($statement, array $params = array())
    {
        $stmt = $this->prepare($statement);
        $stmt->execute($params);
        return $stmt;
    }
    
    /**
     * Prepares and executes a statement, 
     * returns the value of the first column or the first row
     * 
     * @param string $query
     * @param array $params
     * @return mixed
     */
    public function queryValue($statement, array $params = array())
    {
        $stmt = $this->queryParams($statement, $params);
        return $stmt->fetchColumn();
    }
    
    /**
     * {@inheritDoc}
     */
    public function exec($statement)
    {
        return $this->_profileQuery('parent::exec', $statement);
    }
    
    /**
     * Executes the query using the provided $callback and profiles
     * it if a profiler has been defined
     * 
     * @param callback $callback
     * @param string $statement
     */
    protected function _profileQuery($callback, $statement)
    {
        $this->_profiler !== null && $this->_profiler->startQuery($statement, array());
        
        try {
            $returns = call_user_func($callback, $statement);
        } catch (PDOException $e) {
            $this->_profiler !== null && $this->_profiler->stopQuery($e);
            throw $e;
        }
    
        $this->_profiler !== null && $this->_profiler->stopQuery();
        return $returns;
    }
    
    /**
     * @param Cache $cache
     */
    public function setCache(Cache $cache)
    {
        $this->_cache = $cache;
    }
    
    /**
     * @return Cache
     */
    public function getCache()
    {
        if ($this->_cache === null) {
            $this->_cache = Session::getCache();
        }
        return $this->_cache;
    }
    
    /**
     * Returns a cache id generated from an sql string and some parameters
     * 
     * @param string|\ClassQL\SqlString $query
     * @param array $params
     * @return string
     */
    public function cacheId()
    {
        $segments = array();
        foreach (func_get_args() as $arg) {
            if (is_array($arg) || is_object($arg)) {
                $arg = md5(serialize($arg));
            }
            $segments[] = (string) $arg;
        }
        return implode(':', $segments);
    }
    
    /**
     * Executes the closure unless a cache entry with
     * the specified id exists. The closure must return
     * the data to store in the cache. 
     * 
     * Returns the result of the closure of the cached
     * data.
     * 
     * The closure will take the current connection object
     * as first parameter.
     * 
     * @param string $id
     * @param Closure $callback
     * @return mixed
     */
    public function cached($id, \Closure $callback)
    {
        if ($this->getCache()->has($id)) {
            return $this->getCache()->get($id);
        }
        
        $data = $callback($this);
        $this->getCache()->set($id, $data);
        return $data;
    }
    
    /**
     * Executes a SELECT * on $tableName
     * 
     * @param string $query
     * @param array $params
     * @param string $afterWhere
     * @return array
     */
    public function select($tableName, $where = null, $afterWhere = '')
    {
        list($where, $params) = $this->_buildWhere($where);
        $query = "SELECT * FROM $tableName $where $afterWhere";
        $stmt = $this->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Prepares and executes a statement
     * 
     * @param string $query
     * @param string $column
     * @param array $params
     * @return mixed
     */
    public function selectValue($tableName, $column, $where = null)
    {
        list($where, $params) = $this->_buildWhere($where);
        $query = "SELECT $column FROM $tableName $where";
        $stmt = $this->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    /**
     * Executes a SELECT COUNT(*) on $tableName
     * 
     * @param string $tableName
     * @param array|string $where
     * @return int
     */
    public function count($tableName, $where = null)
    {
        list($where, $params) = $this->_buildWhere($where);
        $query = "SELECT COUNT(*) FROM $tableName $where";
        
        $stmt = $this->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    /**
     * Inserts some data into the specified table
     * 
     * @param string $tableName
     * @param array $data
     * @return Statement
     */
    public function insert($tableName, array $data)
    {
        $query = sprintf("INSERT INTO $tableName (%s) VALUES(%s)",
            implode(', ', array_keys($data)), 
            implode(', ', array_fill(0, count($data), '?'))
        );
            
        $stmt = $this->prepare($query);
        $stmt->execute(array_values($data));
        return $stmt;
    }
    
    /**
     * Updates the specified table matching the $where using $data
     * 
     * @param string $tableName
     * @param array $data
     * @param array|string $where
     * @return Statement
     */
    public function update($tableName, array $data, $where = null)
    {
        list($where, $params) = $this->_buildWhere($where);
        
        $set = array();
        foreach ($data as $column => $value) {
            $set[] = "$column = ?";
        }
        $query = "UPDATE $tableName SET " . implode(', ', $set) . " $where";
        $params = array_merge(array_values($data), $params);
        
        $stmt = $this->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }
    
    /**
     * Deletes row from a table
     * 
     * @param string $tableName
     * @param array|string $where
     * @return Statement
     */
    public function delete($tableName, $where = null)
    {
        list($where, $params) = $this->_buildWhere($where);
        $query = "DELETE FROM $tableName $where";
        
        $stmt = $this->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }
    
    /**
     * Builds a condition string
     * 
     * If $where is empty, the returned string will be empty
     * If $where is a string, a WHERE $where string will be returned with no params
     * If $where is an array, each key, value pairs will be converted to a key = value condition
     * and some params will be returned
     * 
     * @param mixed $where
     * @return array (query, params)
     */
    protected function _buildWhere($where)
    {
        if (empty($where)) {
            return array('', array());
        }
        if (is_string($where)) {
            return array("WHERE $where", array());
        }
        return array(
            'WHERE ' . implode(' = ? AND ', array_keys($where)) . ' = ?',
            array_values($where)
        );
    }
}