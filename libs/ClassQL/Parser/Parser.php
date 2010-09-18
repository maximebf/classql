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
 
namespace ClassQL\Parser;

use Parsec\StringParser,
    Parsec\ContextFactory;

class Parser extends StringParser
{
    public function __construct()
    {
        parent::__construct(
            new Lexer(), 
            new ContextFactory(array('ClassQL\Parser\Contexts'))
        );
    }
    
    public function parse($string)
    {
        $raw = $this->parseRaw($string);
        return $this->_compute($raw);
    }
    
    public function parseRaw($string)
    {
        return parent::parse($string, 'File');
    }
    
    public function parseFile($filename)
    {
        return $this->parse(file_get_contents($filename));
    }
    
    protected function _compute($raw)
    {
        $clean = array(
            'namespace' => $raw['namespace'],
            'uses' => $raw['uses'],
            'objects' => array()
        );
        
        foreach ($raw['objects'] as $object) {
            if (isset($objects[$object['name']])) {
                throw new Exception("Cannot redeclare '${object['name']}'");
            }
            
            if ($object['type'] == 'model') {
                $clean['objects'][$object['name']] = $this->_computeModel($object);
            } else {
                $clean['objects'][$object['name']] = $this->_computeOperation($object);
            }
        }
        
        return $clean;
    }
    
    protected function _computeModel($raw)
    {
        $modelName = $raw['name'];
        $tableName = isset($raw['table']) ? $raw['table'] : $modelName;
        
        $clean = array(
            'type' => 'model',
            'name' => $modelName,
            'table' => $tableName,
            'modifiers' => $raw['modifiers'],
            'extends' => isset($raw['extends']) ? $raw['extends'] : '\ClassQL\Model',
            'implements' => isset($raw['implements']) ? $raw['implements'] : array(),
            'columns' => array(),
            'vars' => array($modelName => $tableName),
            'methods' => array(),
            'docComment' => $raw['docComment']
        );
        
        $availableVars = array("$$modelName", '$this');
        foreach ($raw['columns'] as $column) {
            if (in_array($column['name'], $availableVars)) {
                throw new Exception("Cannot redeclare '${column['name']}' in '$modelName'");
            }
            $clean['columns'][$column['name']] = $column;
            $availableVars[] = '$' . $column['name'];
        }
        foreach ($raw['vars'] as $var) {
            if (in_array($var['name'], $availableVars)) {
                throw new Exception("Cannot redeclare '${var['name']}' in '$modelName'");
            }
            
            $block = $this->_computeBlock($var['value'], $clean['vars']);
            $clean['vars'][$var['name']] = $block['sql'];
            $availableVars[] = $var['name'];
        }
        
        foreach ($raw['methods'] as $method) {
            if (isset($clean[$method['name']])) {
                throw new Exception("Cannot redeclare '${method['name']}' in '$modelName'");
            }
            
            $method = $this->_computeOperation($method, $availableVars);
            
            if (empty($method['modifiers']) || 
                count(array_intersect(array('protected', 'private'), $method['modifiers'])) == 0) {
                    $method['modifiers'][] = 'public';
            }
            
            if (isset($method['query'])) {
                $method['query'] = $this->_computeBlock($method['query'], $clean['vars']);
            }
            
            $clean['methods'][$method['name']] = $method;
        }
    
        return $clean;
    }
    
    protected function _computeBlock($block, $availableVars = array())
    {
        $sql = $block['sql'];
        $vars = array_flip($block['vars']);
        foreach ($block['vars'] as $var) {
            if (isset($availableVars[$var])) {
                $sql = str_replace($var, $availableVars[$var], $sql);
                unset($vars[$var]);
            }
        }
        return array(
            'sql' => $sql,
            'vars' => array_keys($vars)
        );
    }
    
    protected function _computeOperation($operation, $possibleVars = array())
    {
        $params = array();
        foreach ($operation['params'] as $param) {
            if (isset($params[$param])) {
                throw new Exception("Parameter '$param' is defined twice in '${operation['name']}'");
            }
            $params[$param] = $param;
        }
        $operation['params'] = $params;
        
        $possibleVars = array_merge($possibleVars, $operation['params']);
        
        if (isset($operation['query'])) {
            $vars = $operation['query']['vars'];
        } else {
            $vars = array();
            foreach ($operation['callback']['args'] as $arg) {
                if ($arg['type'] == 'variable') {
                    $vars[] = $arg['value'];
                }
            }
        }
        
        $neededVars = array();
        foreach ($vars as $var) {
            if (strpos($var, '[') !== false) {
                $var = substr($var, 0, strpos($var, '['));
            }
            $neededVars[] = $var;
        }
        
        $missingVars = array_values(array_diff($neededVars, $possibleVars));
        if (!empty($missingVars)) {
            throw new Exception("Undefined variable '${missingVars[0]}' used in '${operation['name']}'");
        }
        
        return $operation;
    }
}
