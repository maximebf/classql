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

use \PDO;

class Connection extends PDO
{
    const ATTR_NESTED_TRANSACTION = 100;
    
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
        $this->setAttribute(self::ATTR_NESTED_TRANSACTION, true);
    }
    
    /**
     * {@inheritDoc}
     */
    public function beginTransaction()
    {
        if (!$this->getAttribute(self::ATTR_NESTED_TRANSACTION) || $this->_transactionCount++ === 0) {
            parent::beginTransaction();
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function commit()
    {
        if (!$this->getAttribute(self::ATTR_NESTED_TRANSACTION) || $this->_transactionCount-- === 1) {
            parent::commit();
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function rollBack()
    {
        if (!$this->getAttribute(self::ATTR_NESTED_TRANSACTION) || $this->_transactionCount-- === 1) {
            parent::rollBack();
        }
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
    public function update($tableName, array $data, $where)
    {
        $set = array();
        foreach ($data as $column => $value) {
            $set[] = "$column = ?";
        }
        $query = "UPDATE $tableName SET " . implode(', ', $set) . " WHERE ";
        $params = array_values($data);
        
        if (is_string($where)) {
            $query .= $where;
        } else {
            $query .= implode(' = ? AND ', array_keys($where)) . ' = ?';
            $params = array_merge($params, array_values($where));
        }
        
        $stmt = $this->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }
}