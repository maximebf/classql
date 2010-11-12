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

class InlineFunctions extends AliasResolver
{
    /** @var array */
    protected static $_aliases = array(
        'if' => '\ClassQL\InlineFunctions::test',
        'switch' => '\ClassQL\InlineFunctions::switchArray',
        'implode' => '\ClassQL\InlineFunctions::implode',
        'and' => '\ClassQL\InlineFunctions::_and',
        'or' => '\ClassQL\InlineFunctions::_or',
        'set' => '\ClassQL\InlineFunctions::set',
        'where' => '\ClassQL\InlineFunctions::where',
        'composite' => '\ClassQL\InlineFunctions::composite',
        'with' => '\ClassQL\InlineFunctions::with',
        'columns' => '\ClassQL\InlineFunctions::columns',
        'insert' => '\ClassQL\InlineFunctions::insert',
        'update' => '\ClassQL\InlineFunctions::update'
    );
    
    public static function test($expression, $true, $false = null)
    {
        if ($expression) {
            return $true;
        }
        return $false;
    }
    
    public static function switchArray($value, $array, $default = null)
    {
        if (isset($array[$value])) {
            return $array[$value];
        }
        return $default;
    }
    
    public static function implode($separator, $array)
    {
        $parts = array();
        $params = array();
        
        foreach ($array as $key => $value) {
            if ($value instanceof SqlString) {
                $parts[] = $value->sql;
                $params = array_merge($params, $value->params);
            } else if (is_array($value) && !empty($value)) {
                $sqlString = self::implode($separator, $value);
                if (!empty($sqlString->sql) || !empty($sqlString->params)) {
                    $parts[] = $sqlString->sql;
                    $params = array_merge($params, $sqlString->params);
                }
            } else if (is_string($key)) {
                $parts[] = "$key = ?";
                $params[] = $value;
            } else if (!empty($value)) {
                $parts[] = (string) $value;
            }
        }
        
        return new SqlString(implode($separator, $parts), $params);
    }
    
    public static function _and($array)
    {
        $array = func_get_args();
        return self::implode(' AND ', $array);
    }
    
    public static function _or($array)
    {
        $array = func_get_args();
        return self::implode(' OR ', $array);
    }
    
    public static function set($array)
    {
        $array = func_get_args();
        return self::implode(', ', $array);
    }
    
    public static function where($array)
    {
        $conditions = func_get_args();
        $where = self::_and($conditions);
        if (empty($where->sql)) {
            return null;
        }
        return new SqlString("WHERE {$where->sql}", $where->params);
    }
    
    public static function composite($tableName, $columns)
    {
        if (!is_array($columns)) {
            $columns = explode(',', $columns);
        }
        
        foreach ($columns as &$column) {
            $parts = array_map('trim', explode(' as ', $column));
            $columnName = array_shift($parts);
            if (count($parts) > 0) {
                $alias = array_pop($parts);
            } else {
                $columnParts = explode('.', $columnName);
                $alias = array_pop($columnParts);
            }
            $column = "$columnName as {$tableName}__{$alias}";
        }
        
        return implode(', ', $columns);
    }
    
    public static function with($className, $alias = null)
    {
        return self::composite($alias ?: $className::$tableName, self::columns($className));
    }
    
    public static function columns($className)
    {
        $tableName = $className::$tableName;
        return implode(', ', array_map(function($column) use ($tableName) {
            return "$tableName.$column";
        }, $className::$columns));
    }
    
    public static function insert($tableName, $data)
    {
        $sql = sprintf("INSERT INTO $tableName (%s) VALUES(%s)",
            implode(', ', array_keys($data)), 
            implode(', ', array_fill(0, count($data), '?'))
        );
        return new SqlString($sql, array_values($data));
    }
    
    public function update($tableName, $data)
    {
        $sql = "UPDATE $tableName SET " . implode(' = ?, ', array_keys($data)) . " = ?";
        return new SqlString($sql, array_values($data));
    }
}
