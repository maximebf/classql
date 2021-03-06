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
 
namespace ClassQL\Generator;

use ClassQL\InlineFunctions,
    ClassQL\Database\Type,
    ClassQL\Exception;

class PHPGenerator extends AbstractGenerator
{
    protected $_currentNamespace;
    
    protected $_currentClass;
    
    protected $_currentOperation;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_templates =  array(
            'file'      => __DIR__ . '/PHPTemplates/File.php',
            'function'  => __DIR__ . '/PHPTemplates/Function.php',
            'class'     => __DIR__ . '/PHPTemplates/Class.php',
        );
    }
    
    /**
     * Generates a PHP file from the output of the parser
     * 
     * @param array $ast
     * @return string
     */
    protected function _generateFile(array $ast)
    {
        $this->_currentNamespace = $ast['namespace'];
        
        foreach ($ast['objects'] as &$object) {
            if ($object['type'] == 'function') {
                $object = $this->_generateFunction($object);
            } else {
                $object = $this->_generateClass($object);
            }
        }
        
        return $this->_renderTemplate('file', $ast);
    }
    
    /**
     * Generates PHP code for functions
     * 
     * @param array $function
     * @return string
     */
    protected function _generateFunction($function)
    {
        $this->_currentClass = false;
        $function['execute_func_name'] = 'execute_' . $function['name'];
        $function['statement_func_name'] = 'get_statement_for_' . $function['name'];
        $function['query_func_name'] = 'get_query_for_' . $function['name'];
        $function['filter_func_name'] = 'filter_results_for_' . $function['name'];
        return $this->_generateOperation($function);
    }
    
    /**
     * Generates PHP for classes
     * 
     * @param array $class
     * @return string
     */
    protected function _generateClass($class)
    {
        $this->_currentClass = $class;
        foreach ($class['methods'] as &$method) {
            $method['execute_func_name'] = 'execute' . ucfirst($method['name']);
            $method['statement_func_name'] = 'getStatementFor' . ucfirst($method['name']);
            $method['query_func_name'] = 'getQueryFor' . ucfirst($method['name']);
            $method['filter_func_name'] = 'filterResultsFor' . ucfirst($method['name']);
            $method['php'] = str_replace("\n", "\n    ", $this->_generateOperation($method));
        }
        return $this->_renderTemplate('class', $class);
    }
    
    /**
     * Generates code for functions or methods
     * 
     * @param array $operation
     * @return string
     */
    protected function _generateOperation($operation)
    {
        $this->_currentOperation = $operation;
        return $this->_renderTemplate('function', $operation);
    }
    
    /**
     * Renders a list of modifiers
     * 
     * @param array $modifiers
     * @return string
     */
    protected function _renderModifiers($modifiers)
    {
        $modifiers = array_diff($modifiers, array('virtual'));
        return count($modifiers) ? implode(' ', $modifiers) . ' ' : '';
    }
    
    /**
     * Renders the self:: or $this-> depending on the type and modifiers
     * 
     * @param string $type
     * @param array $modifiers
     */
    protected function _renderScope($modifiers)
    {
        if (in_array('static', $modifiers)) {
            return 'self::';
        } else if ($this->_currentClass) {
            return '$this->';
        }
        return '';
    }
    
    /**
     * @param array $args
     * @param array $varsInScope
     * @return string
     */
    protected function _renderArgs($args, $varsInScope = array())
    {
        $renderedArgs = array();
        foreach ($args as $arg) {
            $renderedArgs[] = $this->_getRenderedArgsItem($arg, $varsInScope);
        }
        return implode(', ', $renderedArgs);
    }
    
    /**
     * @param array $array
     * @param array $varsInScope
     * @return string
     */
    protected function _renderArray($array, $varsInScope = array())
    {
        $elements = array();
        foreach ($array as $element) {
            $item = $this->_getRenderedArgsItem($element, $varsInScope);
            if (isset($element['key'])) {
                $elements[] = "'${element['key']}' => $item";
            } else {
                $elements[] = $item;
            }
        }
        return 'array(' . implode(', ', $elements) . ')';
    }
    
    /**
     * @param array $item
     * @return string
     */
    protected function _getRenderedArgsItem($item, $varsInScope = array())
    {
        if ($item['type'] == 'variable') {
            return $this->_renderVar($item['value'], $varsInScope);
        }
        if ($item['type'] == 'array') {
            return $this->_renderArray($item['value'], $varsInScope);
        }
        if ($item['type'] == 'identifier') {
            return "'" . $this->_resolveClassName($item['value']) . "'";
        }
        if ($item['type'] == 'callback') {
            return "'" . $this->_resolveCallback($item['value']) . "'";
        }
        if ($item['type'] == 'sql') {
            return "new \\ClassQL\\SqlString(\"" . $this->_renderQuery($item['value'], $varsInScope) 
                 . "\", " . $this->_renderQueryParams($item['value'], $varsInScope) . ")";
        }
        if ($item['type'] == 'function') {
            return $this->_renderInlineFunc($item['value'], $varsInScope);
        }
        return $item['value'];
    }
    
    /**
     * @param string $query
     * @param array $varsToParameterized
     * @return string
     */
    protected function _renderQuery($query, $inScope)
    {
        $sql = str_replace('"', '\"', $query['sql']);
        foreach ($query['vars'] as $var) {
            if ($var == '$this') {
                $sql = str_replace('$this', '" . self::$tableName . "', $sql); 
            } else if (isset($query['inlines'][$var])) {
                if ($query['inlines'][$var]['type'] == 'function') {
                    $sql = str_replace($var, "{{$var}->sql}", $sql);
                } else {
                    $sql = str_replace($var, '" . ' . $query['inlines'][$var]['expression'] . ' . "', $sql);
                }
            } else if (substr($var, 0, 2) == '$$') {
                $sql = str_replace($var, '" . ' . $this->_renderVar(substr($var, 1), $inScope) . ' . "', $sql);
            } else {
                $sql = str_replace($var, '?', $sql);
            }
        }
        return $sql;
    }
    
    /**
     * @param array $vars
     * @param array $params
     * @return string
     */
    protected function _renderQueryParams($query, $inScope)
    {
        $params = array();
        $currentParams = array();
        foreach ($query['vars'] as $var) {
            if (isset($query['inlines'][$var])) {
                if ($query['inlines'][$var]['type'] == 'function') {
                if (!empty($currentParams)) {
                        $params[] = 'array(' . implode(', ', $currentParams) . ')';
                        $currentParams = array();
                    }
                    $params[] = "{$var}->params";
                }
            } else if ($var != '$this' && substr($var, 0, 2) != '$$') {
                $currentParams[] = $this->_renderVar($var, $inScope);
            }
        }
        
        if (!empty($currentParams)) {
            $params[] = 'array(' . implode(', ', $currentParams) . ')';
        }
        
        if (count($params) > 1) {
            return 'array_merge(' . implode(', ', $params) . ')';
        } else if (!empty($params)) {
            return $params[0];
        }
        return 'array()';
    }
    
    /**
     * @param string $var
     * @param array $varsInScope
     * @return string
     */
    protected function _renderVar($var, $varsInScope = false)
    {
        if ($var === '$this') {
            $var = 'self::$tableName';
        } else if (strpos($var, '::') !== false) {
            $var = substr(str_replace('::', '::$', $var), 1);
        } else if ($varsInScope === true || ($varsInScope !== false 
            && !in_array($this->_getCanonicalVarName($var), $varsInScope))) {
                if (in_array('static', $this->_currentOperation['modifiers'])) {
                    $var = "self::$var";
                } else {
                    $var = '$this->' . substr($var, 1);
                }
        }
        return str_replace(array('[', ']'), array("['", "']"), $var);
    }
    
    protected function _renderMappingInfo($mappingInfo)
    {
        if (!isset($mappingInfo['with'])) {
            return 'array()';
        }
        
        $array = array();
        foreach ($mappingInfo['with'] as $mapClass) {
            $array[] = "'{$mapClass['property']}' => array('classname' => '" 
                     . $this->_resolveClassName($mapClass['value']) 
                     . "', 'properties' => " . $this->_renderMappingInfo($mapClass)
                     . ", 'array' => " . ($mapClass['type'] == 'collection' ? 'true' : 'false') . ")";
        }
        return sprintf('array(%s)', implode(', ', $array));
    }
    
    /**
     * @param string $var
     * @return string
     */
    protected function _getCanonicalVarName($var)
    {
        if (strpos($var, '[')) {
            return substr($var, 0, strpos($var, '['));
        }
        return $var;
    }
    
    protected function _renderInlineFunc($function, $varsInScope = array())
    {
        return sprintf('%s(%s)',
            $this->_getInlineFuncName($function['name']),
            $this->_renderArgs($function['args'], $varsInScope)
        );
    }
    
    /**
     * Returns the string used to call a function
     * 
     * @param string $name
     * @return string
     */
    protected function _getInlineFuncName($name)
    {
        if ($this->_currentClass && isset($this->_currentClass['methods'][$name])) {
            $methodName = 'getQueryFor' . ucfirst($name);
            if (in_array('static', $this->_currentClass['methods'][$name]['modifiers'])) {
                return 'self::' . $methodName;
            }
            return '$this->' . $methodName;
        }
        
        if (($realName = InlineFunctions::resolveAlias($name)) !== null) {
            return $realName;
        }
        return $name;
    }
    
    protected function _resolveClassName($identifier)
    {
        if ($this->_currentNamespace !== null && substr($identifier, 0, 1) !== '\\') {
            return '\\' . trim($this->_currentNamespace, '\\') . '\\' . $identifier;
        }
        return $identifier;
    }

    protected function _resolveCallback($callback) {
        $parts = explode('::', $callback);
        return $this->_resolveClassName($parts[0]) . "::" . $parts[1];
    }
    
    protected function _getMappedColumnTypes()
    {
        $types = array();
        foreach ($this->_currentClass['columns'] as $column) {
            if (Type::hasType($column['type'])) {
                $types[$column['name']] = Type::getTypeClassName($column['type']);
            }
        }
        return $types;
    }
    
    protected function _hasMappedColumns()
    {
        foreach ($this->_currentClass['columns'] as $column) {
            if (Type::hasType($column['type'])) {
                return true;
            }
        }
        return false;
    }
    
    protected function _hasAttribute($attributes, $name) {
        foreach ($attributes as $attr) {
            if ($attr['name'] == $name) {
                return true;
            }
        }
        return false;
    }
    
    protected function _getAttribute($attributes, $name, $default = null) {
        $args = $this->_getAttributeArgs($attributes, $name);
        if (count($args)) {
            return array_shift($args);
        }
        return $default;
    }
    
    protected function _getAttributeValue($attributes, $name, $default = '') {
        if ($var = $this->_getAttribute($attributes, $name)) {
            return trim($var['value'], "'");
        }
        return $default;
    }
    
    protected function _getAttributeArgs($attributes, $name) {
        foreach ($attributes as $attr) {
            if ($attr['name'] == $name) {
                return $attr['args'];
            }
        }
        return array();
    }

    protected function _getAttributes($attributes, $name) {
        $attrs = array();
        foreach ($attributes as $attr) {
            if ($attr['name'] == $name) {
                $attrs[] = $attr;
            }
        }
        return $attrs;
    }

    protected function _renderCacheIdArgs($attributes, $attributeName = 'CacheId') {
        if (!$this->_hasAttribute($attributes, $attributeName)) {
            return "__CLASS__, __METHOD__, func_get_args()";
        }
        $args = $this->_getAttributeArgs($attributes, $attributeName);
        return $this->_renderCacheIdArgsFromArgs($args);
    }

    protected function _renderCacheIdArgsFromArgs($args) {
        if (count($args) == 0) {
            throw new Exception("Missing argument for CacheId attribute");
        }
        return $this->_renderArgs($args, array_keys($this->_currentOperation['params']));
    }
    
    protected function _getIdentityResolver() {
        foreach ($this->_currentClass['methods'] as $method) {
            if ($this->_hasAttribute($method['attributes'], 'IdentityResolver')) {
                if (!in_array('static', $method['modifiers'])) {
                    throw new Exception('Only static method can be identity resolvers');
                }
                $callback = $this->_resolveClassName($this->_currentClass['name']) . '::' . $method['name'];
                return array($callback, $method);
            }
        }

        if ($this->_hasAttribute($this->_currentClass['attributes'], 'IdentityResolver')) {
            $args = $this->_getAttributeArgs($this->_currentClass['attributes'], 'IdentityResolver');
            if (empty($args)) {
                throw new Exception('Missing argument for IdentityResolver attribute on class');
            }
            if ($args[0]['type'] != 'callback') {
                throw new Exception('Argument 1 of IdentityResolver attribute on class must be a callback');
            }
            return array($this->_resolveCallback($args[0]['value']), array());
        }

        throw new Exception('No identity resolvers registered for this class');
    }

    protected function _getConnectionName() {
        if ($this->_hasAttribute($this->_currentClass['attributes'], 'Connection')) {
            return "'" . $this->_getAttributeValue($this->_currentClass['attributes'], 'Connection') . "'";
        }
        return '';
    }
}
