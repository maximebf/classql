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

class PHPGenerator extends AbstractGenerator
{
    public $templates = array(
        'file'      => 'ClassQL/Generator/PHPTemplates/File.php',
        'function'  => 'ClassQL/Generator/PHPTemplates/Function.php',
        'class'     => 'ClassQL/Generator/PHPTemplates/Class.php',
    );
    
    /**
     * Generates a PHP file from the output of the parser
     * 
     * @param array $descriptor
     * @return string
     */
    protected function _generateFile(array $descriptor)
    {
        foreach ($descriptor['objects'] as &$object) {
            if ($object['type'] == 'function') {
                $object = $this->_generateFunction($object);
            } else {
                $object = $this->_generateClass($object);
            }
        }
        
        return $this->_renderTemplate('file', $descriptor);
    }
    
    /**
     * Generates PHP code for functions
     * 
     * @param array $function
     * @return string
     */
    protected function _generateFunction($function)
    {
        $function['execute_func_name'] = 'execute_' . $function['name'];
        $function['statement_func_name'] = 'get_statement_for_' . $function['name'];
        $function['filter_func_name'] = 'filter_results_for_' . $function['name'];
        $function['class'] = false;
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
        foreach ($class['methods'] as &$method) {
            $method['execute_func_name'] = 'execute' . ucfirst($method['name']);
            $method['statement_func_name'] = 'getStatementFor' . ucfirst($method['name']);
            $method['filter_func_name'] = 'filterResultsFor' . ucfirst($method['name']);
            $method['class'] = $class;
            $method = $this->_generateOperation($method);
            $method = str_replace("\n", "\n    ", $method);
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
    protected function _renderScope($type, $modifiers)
    {
        if (in_array('static', $modifiers)) {
            return 'self::';
        } else if ($type == 'method') {
            return '$this->';
        }
        return '';
    }
    
    /**
     * @param array $args
     * @param array $varsInScope
     * @param string $divider
     * @return string
     */
    protected function _renderArgs($args, $varsInScope = array(), $divider = ', ')
    {
        $renderedArgs = array();
        foreach ($args as $arg) {
            $renderedArgs[] = $this->_getRenderedArgsItem($arg, $varsInScope);
        }
        return implode($divider, $renderedArgs);
    }
    
    /**
     * @param array $array
     * @param string $divider
     * @return string
     */
    protected function _renderArray($array, $divider = ', ')
    {
        $elements = array();
        foreach ($array as $element) {
            $item = $this->_getRenderedArgsItem($element);
            if (isset($element['key'])) {
                $elements[] = "'${element['key']}' => $item";
            } else {
                $elements[] = $item;
            }
        }
        return 'array(' . implode($divider, $elements) . ')';
    }
    
    /**
     * @param array $item
     * @return string
     */
    protected function _getRenderedArgsItem($item, $varsInScope = array())
    {
        if ($item['type'] == 'variable' && !in_array($item['value'], $varsInScope)) {
            return '$this->' . substr($item['value'], 1);
        }
        if ($item['type'] == 'array') {
            return $this->_renderArray($item['value']);
        }
        if ($item['type'] == 'identifier') {
            return "'${item['value']}'";
        }
        return $item['value'];
    }
    
    /**
     * @param string $query
     * @param array $varsToParameterized
     * @return string
     */
    protected function _renderQuery($query)
    {
        $sql = str_replace("'", "\'", $query['sql']);
        foreach ($query['vars'] as $var) {
            if ($var == '$this') {
                $sql = str_replace('$this', "' . self::\$tableName . '", $sql); 
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
    protected function _renderQueryParams($vars, $inScope)
    {
        $params = array();
        foreach ($vars as $var) {
            $varname = $this->_getCanonicalVarName($var);
            $var = str_replace(array('[', ']'), array("['", "']"), $var);
            if (in_array($varname, $inScope)) {
                $params[] = $var;
            } else if ($var !== '$this') {
                $params[] = '$this->' . substr($var, 1);
            }
        }
        return implode(', ', $params);
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
        } else if ($varsInScope === true || ($varsInScope !== false && !in_array($var, $varsInScope))) {
            $var = '$this->' . substr($var, 1);
        }
        return str_replace(array('[', ']'), array("['", "']"), $var);
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
}
