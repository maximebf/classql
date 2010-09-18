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
        'file'      => 'ClassQL/Generator/Templates/File.php',
        'function'  => 'ClassQL/Generator/Templates/Function.php',
        'class'     => 'ClassQL/Generator/Templates/Class.php',
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
    
    protected function _formatParams($params)
    {
        
    }
    
    protected function _formatArguments($args)
    {
        
    }
    
    protected function _parameterizeQuery($query)
    {
        return preg_replace('/\$[a-z0-9A-Z_]+/', '?', $query);
    }
}
