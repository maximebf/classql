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
    \PDOStatement, 
    \PDOException;

/**
 * Custom statement that adds profiling capabilities
 */
class Statement extends PDOStatement
{
    /** @var Profiler */
    protected $_profiler;
    
    /** @var array */
    protected $_fetchedCompositedRows;
    
    /** @var int */
    protected $_fetchMode;
    
    /** @var array */
    protected $_fetchCompositeInfo;
    
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
    public function setFetchMode($mode)
    {
        $args = func_get_args();
        $this->_fetchMode = $mode;
        if ($mode === Connection::FETCH_COMPOSITE) {
            array_shift($args);
            $this->_fetchCompositeInfo = $args;
        } else {
            call_user_func_array('parent::setFetchMode', $args);
        }
    }
    
    /**
     * @return int
     */
    public function getFetchMode()
    {
        return $this->_fetchMode;
    }
    
    /**
     * {@inheritDoc}
     */
    public function fetch()
    {
        $args = func_get_args();
        if ($this->_fetchMode === Connection::FETCH_COMPOSITE) {
            return call_user_func_array(array($this, 'fetchComposite'), $this->_fetchCompositeInfo);
        }
        return call_user_func_array('parent::fetch', $args);
    }
    
    /**
     * {@inheritDoc}
     */
    public function fetchAll()
    {
        $args = func_get_args();
        if ($this->_fetchMode === Connection::FETCH_COMPOSITE) {
            return call_user_func_array(array($this, 'fetchAllComposite'), $this->_fetchCompositeInfo);
        }
        return call_user_func_array('parent::fetchAll', $args);
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
    
    /**
     * Fetches the next row using the composite fetch mode
     * 
     * Note: this will use {@see fetchAllComposite} and then keep
     * fetched rows in memory. This is mandatory as rows in composite mode
     * can span multiple rows in the returned data
     * 
     * @param string $className
     * @param array $mapping
     * @return object
     */
    public function fetchComposite($className = null, $mapping = array())
    {
        if ($this->_fetchedCompositedRows === null) {
            $this->_fetchedCompositedRows = $this->fetchAllComposite($className, $mapping);
        }
        return array_shift($this->_fetchedCompositedRows) ?: false;
    }
    
    /**
     * Fetch all rows and create objects following the mapping rules provided
     * 
     * @param string $className
     * @param array $mapping
     * @return array
     */
    public function fetchAllComposite($className, $mapping = array())
    {
        if (($rows = $this->fetchAll(PDO::FETCH_ASSOC)) === false) {
            return false;
        }
        
        $all = array();
        $mapping = array(
            'classname' => $className,
            'properties' => $mapping
        );
        
        foreach ($rows as $row) {
            $row = $this->_dimensionizeRow($row);
            $this->_objectifyRow($row, $mapping, $all);
        }
        return $all;
    }
    
    /**
     * Transforms a flat array into a multi-dimensional array,
     * splitting keys using the specified separator
     * 
     * @param array $data
     * @param string $separator
     * @return array
     */
    protected function _dimensionizeRow($data, $separator = '__')
    {
        $dimensionized = array();
        foreach ($data as $key => $value) {
            $keyParts = explode($separator, $key);
            $column = array_pop($keyParts);
            $parent = &$dimensionized;
            foreach ($keyParts as $part) {
                if (!isset($parent[$part])) {
                    $parent[$part] = array();
                }
                $parent = &$parent[$part];
            }
            $parent[$column] = $value;
        }
        return $dimensionized;
    }
    
    /**
     * Create objects from a dimensionized row array
     * 
     * If $parent is specified and an object with the same row id
     * exists, it will be reused. Also, new objects will be added to $parent.
     * 
     * $mapping should be an array where key as the mapped properties and their
     * value an array containing:
     *   - classname: the class name of objects inside this property
     *   - array: true or false indicating whether this property is an array of object
     *   - properties: mapping for child properties
     * As a shortcut, this array can be replaced with the classname only.
     * 
     * @param array $row
     * @param array $mapping
     * @param array $parent
     * @return mixed
     */
    protected function _objectifyRow($row, $mapping, &$parent = null)
    {
        list($row, $composited) = $this->_extractComposited($row);
        $rowId = md5(implode('', $row));
        
        if ($parent === null || !isset($parent[$rowId])) {
            $instance = $this->_createInstance($mapping['classname'], $row);
            if ($parent !== null) {
                $parent[$rowId] = $instance;
            }
        } else {
            $instance = $parent[$rowId];
        }
        
        // mapped properties
        $props = isset($mapping['properties']) ? $mapping['properties'] : array();
        foreach ($composited as $prop => $data) {
            if (!is_array($props[$prop])) {
                $props[$prop] = array('classname' => $props[$prop]);
            }
            if (!isset($props[$prop])) {
                // no mapping info
                $instance->$prop = $data;
            } else if (isset($props[$prop]['array']) && $props[$prop]['array']) {
                // property in an array of object
                if (!isset($instance->$prop)) {
                    $instance->$prop = array();
                }
                $this->_objectifyRow($data, $props[$prop], $instance->{$prop});
            } else {
                // property is a single object
                $instance->$prop = $this->_objectifyRow($data, $props[$prop]);
            }
        }
        
        if ($parent === null) {
            return $instance;
        }
        return $parent;
    }
    
    /**
     * Extract composited properties from the row's columns
     * 
     * @param array $data
     * @param bool $recursive
     * @return array (row, compositedProperties)
     */
    protected function _extractComposited($data, $recursive = false)
    {
        $row = array();
        $composited = array();
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if ($recursive) {
                    $value = $this->_extractComposited($value);
                }
                $composited[$key] = $value;
            } else {
                $row[$key] = $value;
            }
        }
        return array($row, $composited);
    }
    
    /**
     * Creates an object and populates its properties
     * 
     * @param string $className
     * @param array $data
     * @return object
     */
    protected function _createInstance($className, $data)
    {
        $instance = new $className();
        foreach ($data as $key => $value) {
            $instance->{$key} = $value;
        }
        return $instance;
    }
}
