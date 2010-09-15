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

use Parsec\StringParser,
    Parsec\ContextFactory;

class Parser extends StringParser
{
    public function __construct()
    {
        parent::__construct(
            new Lexer(), 
            new ContextFactory(array('ClassQL\\ParserContexts'))
        );
    }
    
    public function parse($string)
    {
        $raw = parent::parse($string, 'File');
        return $this->_compute($raw);
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
                throw new ParserException("Cannot redeclare '${object['name']}'");
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
            'table' => $tableName,
            'extends' => isset($raw['extends']) ? $raw['extends'] : null,
            'implements' => isset($raw['implements']) ? $raw['implements'] : array(),
            'columns' => array(),
            'vars' => array($modelName => $tableName),
            'methods' => array()
        );
        
        $availableVars = array($modelName);
        foreach ($raw['columns'] as $column) {
            if (in_array($column['name'], $availableVars)) {
                throw new ParserException("Cannot redeclare '${column['name']}' in '$modelName'");
            }
            $clean['columns'][$column['name']] = $column;
            $availableVars[] = $column['name'];
        }
        foreach ($raw['vars'] as $var) {
            if (in_array($var['name'], $availableVars)) {
                throw new ParserException("Cannot redeclare '${var['name']}' in '$modelName'");
            }
            
            $block = $this->_computeBlock($var['value'], $clean['vars']);
            $clean['vars'][$var['name']] = $block['sql'];
            $availableVars[] = $var['name'];
        }
        
        foreach ($raw['methods'] as $method) {
            if (isset($clean[$method['name']])) {
                throw new ParserException("Cannot redeclare '${method['name']}' in '$modelName'");
            }
            
            $method = $this->_computeOperation($method, $availableVars);
            
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
                $sql = str_replace("\$$var", $availableVars[$var], $sql);
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
        if (in_array('*', $operation['params'])) {
            return $operation;
        }
        $possibleVars = array_merge($possibleVars, $operation['params']);
        
        if (isset($operation['query'])) {
            $neededVars = $operation['query']['vars'];
        } else {
            $neededVars = array();
            foreach ($operation['callback']['args'] as $arg) {
                if ($arg['type'] == 'variable') {
                    $neededVars[] = $arg['value'];
                }
            }
        }
        
        $missingVars = array_values(array_diff($neededVars, $possibleVars));
        if (!empty($missingVars)) {
            throw new ParserException("Undefined variable '${missingVars[0]}' used in '${operation['name']}'");
        }
        
        return $operation;
    }
}
