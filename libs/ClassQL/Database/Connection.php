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
    \PDOException;

/**
 * Thin wrapper around PDO
 */
class Connection extends PDO
{
    /** @var bool */
    protected $_nestedTransactions = true;
    
    /** @var int */
    protected $_transactionCount = 0;
    
    /** @var Profiler */
    protected $_profiler;
    
    /**
     * {@inheritDoc}
     */
    public function __construct($dsn, $username = null, $passwd = null, array $options = array())
    {
        parent::__construct($dsn, $username, $passwd, $options);
        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('ClassQL\Database\Statement', array()));
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    /**
     * @param Profiler $profiler
     */
    public function setProfiler(Profiler $profiler)
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
     * {@inheritDoc}
     */
    public function query($statement)
    {
        return $this->_profileQuery('parent::query', $statement);
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
     * Prepares and executes a statement
     * 
     * @param string $query
     * @param array $params
     * @return Statement
     */
    public function select($query, array $params = array())
    {
        $stmt = $this->prepare($query);
        $stmt->execute($params);
        return $stmt;
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