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

/**
 * Parses ClassQL's syntax and returns a descriptor array
 */
class Parser extends StringParser
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(
            new Lexer(), 
            new ContextFactory(array('ClassQL\Parser\Contexts'))
        );
    }
    
    /**
     * Parses a ClassQL string and returns a computed descriptor
     * 
     * @return array
     */
    public function parse($string)
    {
        $descriptor = $this->parseRaw($string);
        return $this->_compute($descriptor);
    }
    
    /**
     * Returns the raw descriptor from the parser
     * 
     * @return array
     */
    public function parseRaw($string)
    {
        return parent::parse($string, 'File');
    }
    
    /**
     * Parses a file
     * 
     * @param string $filename
     * @return array
     */
    public function parseFile($filename)
    {
        return $this->parse(file_get_contents($filename));
    }
    
    /**
     * Computes a raw descriptor
     * 
     * @param array $descriptor
     * @return array
     */
    protected function _compute($descriptor)
    {
        foreach ($descriptor['objects'] as &$object) {
            if ($object['type'] == 'class') {
                $object = $this->_computeModel($object);
            } else {
                $object = $this->_computeFunction($object);
            }
        }
        
        return $descriptor;
    }
    
    /**
     * Computes a raw model descriptor
     * 
     * @param array $model
     * @return array
     */
    protected function _computeModel($model)
    {
        $model = array_merge(array(
            'table' => $model['name'],
            'extends' => null,
            'implements' => array()
        ), $model);
        
        foreach ($model['vars'] as &$var) {
            if ($var['type'] !== 'sql') {
                continue;
            }

            $query = $this->_replaceVars($var['value'], $model['vars']);
            $var['value'] = $query['sql'];
        }
        
        foreach ($model['methods'] as &$method) {
            if (isset($method['query'])) {
                if (!isset($method['query']['returns'])) {
                    $method['query']['returns'] = array(
                        'type' => 'collection',
                        'value' => 'self'
                    );
                }
                $method['query']['returns'] = $this->_computeReturns($method['query']['returns'],
                    '\\' . $model['namespace'] . '\\' . $model['name']);
                
                if (isset($method['query'])) {
                    $method['query'] = $this->_replaceVars($method['query'], $model['vars']);
                }
            }
        }
    
        return $model;
    }
    
    /**
     * Computes a raw function descriptor
     * 
     * @param array $function
     * @return array
     */
    protected function _computeFunction($function)
    {
        if (isset($function['query'])) {
            if (!isset($function['query']['returns'])) {
                $function['query']['returns'] = array('type' => 'object');
            }
            $function['query']['returns'] = $this->_computeReturns($function['query']['returns']);
        }
        return $function;
    }
    
    /**
     * Computes a raw returns descriptor
     * 
     * @param array $returns
     * @param string $className
     * @return array
     */
    protected function _computeReturns($returns, $className = '\stdClass')
    {
        if ($returns['type'] == 'class' || $returns['type'] == 'collection') {
            if ($returns['value'] == 'self') {
                $returns['value'] = $className;
            }
        } else if ($returns['type'] == 'object') {
            $returns = array(
                'type' => 'collection',
                'value' => '\stdClass'
            );
        }
        
        if (isset($returns['with'])) {
            foreach ($returns['with'] as &$with) {
                $with = $this->_computeReturns($with);
            }
        }
        
        return $returns;
    }
    
    /**
     * Replaces vars in a query
     * 
     * @param array $query
     * @param array $vars
     * @return array
     */
    protected function _replaceVars($query, $vars)
    {
        $sql = $query['sql'];
        $queryVars = array_flip($query['vars']);
        
        foreach ($query['vars'] as $var) {
            $varname = $var;
            if (strpos($var, '[')) {
                $varname = substr($var, 0, strpos($var, '['));
            }
            
            if (isset($vars[$varname])) {
                $sql = str_replace($var, $vars[$varname]['value'], $sql);
                unset($queryVars[$var]);
            }
        }
        
        return array_merge($query, array('sql' => $sql, 'vars' => array_flip($queryVars)));
    }
}