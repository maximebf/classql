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

class PHPGenerator implements Generator
{
    protected $_templates = array(
        'file'      => 'ClassQL/Generator/PHPTemplates/File.php',
        'function'  => 'ClassQL/Generator/PHPTemplates/Function.php',
        'class'     => 'ClassQL/Generator/PHPTemplates/Class.php',
    );
    
    protected $_namespace;
    
    public function generate(array $descriptor)
    {
        return $this->_generateFile($descriptor);
    }
    
    protected function _generateFile($descriptor)
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
    
    protected function _generateFunction($function)
    {
        $function['execute_func_name'] = 'execute_' . $function['name'];
        $function['statement_func_name'] = 'get_statement_for_' . $function['name'];
        $function['filter_func_name'] = 'filter_results_for_' . $function['name'];
        $function['scope'] = '';
        return $this->_generateOperation($function);
    }
    
    protected function _generateClass($class)
    {
        foreach ($class['methods'] as &$method) {
            $method['execute_func_name'] = 'execute' . ucfirst($method['name']);
            $method['statement_func_name'] = 'getStatementFor' . ucfirst($method['name']);
            $method['filter_func_name'] = 'filterResultsFor' . ucfirst($method['name']);
            $method['scope'] = '$this->';
            $method = $this->_generateOperation($method);
            $method = str_replace("\n", "\n    ", $method);
        }
        return $this->_renderTemplate('class', $class);
    }
    
    protected function _generateOperation($operation)
    {
        return $this->_renderTemplate('function', $operation);
    }
    
    protected function _renderTemplate($template, array $vars = array())
    {
        extract($vars);
        ob_start();
        include $this->_templates[$template];
        return ob_get_clean();
    }
    
    protected function _renderModifiers($modifiers, $isMethod = false)
    {
        $modifiers = array_diff($modifiers, array('virtual'));
        if ($isMethod && !in_array('private', $modifiers) && !in_array('protected', $modifiers)) {
            $modifiers[] = 'public';
        }
        return count($modifiers) ? implode(' ', $modifiers) . ' ' : '';
    }
    
    protected function _renderArgs($args, $varsInScope = array(), $divider = ', ')
    {
        $renderedArgs = array();
        foreach ($args as $arg) {
            $renderedArgs[] = $this->_getRenderedArgsItem($arg, $varsInScope);
        }
        return implode($divider, $renderedArgs);
    }
    
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
    
    protected function _getRenderedArgsItem($item, $varsInScope = false)
    {
        if ($item['type'] === 'variable') {
            return $this->_renderVar($item['value'], $varsInScope);
        }
        if ($item['type'] == 'array') {
            return $this->_renderArray($item['value']);
        }
        if ($item['type'] == 'identifier') {
            return "'${item['value']}'";
        }
        return $item['value'];
    }
    
    protected function _renderVar($var, $varsInScope = false)
    {
        if ($var === '$this') {
            $var = '$this->tableName';
        } else if ($varsInScope === true || ($varsInScope !== false && !in_array($var, $varsInScope))) {
            $var = '$this->' . substr($var, 1);
        }
        return str_replace(array('[', ']'), array("['", "']"), $var);
    }
    
    protected function _renderQuery($query, $varsToParameterized = array())
    {
        $sql = $query['sql'];
        foreach ($varsToParameterized as $var) {
            $sql = str_replace($var, '?', $sql);
        }
        return str_replace("'", "\'", $sql);
    }
    
    protected function _renderQueryInClass($query, $params = array())
    {
        $vars = array();
        foreach ($query['vars'] as $var) {
            if (!in_array($this->_getCanonicalVarName($var), $params)) {
                $vars[] = $this->_renderVar($var, true);
            }
        }
        
        $sql = $this->_renderQuery($query, $this->_getScopeVars($query['vars'], $params));
        foreach ($this->_getClassVars($query['vars'], $params) as $var) {
            $sql = str_replace($var, '%s', $sql);
        }
        
        if (empty($vars)) {
            return "'$sql'";
        } else {
            return "sprintf(\n        '$sql',\n        " 
                 . implode(",\n        ", $vars) . "\n    )";
        }
    }
    
    protected function _renderQueryParams($vars, $params)
    {
        return implode(', ', array_map(array($this, '_renderVar'), 
            $this->_getScopeVars($vars, $params)));
    }
    
    protected function _getCanonicalVarName($var)
    {
        if (strpos($var, '[')) {
            return substr($var, 0, strpos($var, '['));
        }
        return $var;
    }
    
    protected function _getClassVars($vars, $params)
    {
        $classVars = array();
        foreach ($vars as $var) {
            if (!in_array($this->_getCanonicalVarName($var), $params)) {
                $classVars[] = $var;
            }
        }
        return $classVars;
    }
    
    protected function _getScopeVars($vars, $params)
    {
        $scopeVars = array();
        foreach ($vars as $var) {
            if (in_array($this->_getCanonicalVarName($var), $params)) {
                $scopeVars[] = $var;
            }
        }
        return $scopeVars;
    }
}
