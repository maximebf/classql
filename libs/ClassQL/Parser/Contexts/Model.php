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
 
namespace ClassQL\Parser\Contexts;

use ClassQL\Parser\ContainerContext;

class Model extends ContainerContext
{
    protected $_columns = array();
    
    protected $_vars = array();
    
    protected $_methods = array();
    
    protected $_nextName;
    
    public function tokenString($value)
    {
        if ($this->_nextName === null) {
            $this->_nextName = $value;
            return;
        }
        
        $sql = $this->enterContext('Line');
        $column = array(
            'name' => $this->_nextName,
            'type' => $value,
            'sql' => trim($this->_nextName . ' ' . $value . $sql)
        );
        
        $this->_columns[] = $column;
        $this->_nextName = null;
    }
    
    public function tokenCurlyOpen()
    {
        if ($this->_nextName === null) {
            $this->_syntaxError('curlyOpen');
        }
        
        $this->_vars[] = array(
            'name' => '$' . $this->_nextName,
            'value' => $this->enterContext('Block')
        );
        $this->_nextName = null;
    }
    
    public function tokenParenthOpen()
    {
        if ($this->_nextName === null) {
            $this->_syntaxError('parenthOpen');
        }
        
        $params = $this->enterContext('Parameters');
        $method = $this->enterContext('Operation');
        $method['name'] = $this->_nextName;
        $method['params'] = $params;
        $method['modifiers'] = $this->_latestModifiers;
        $method['filters'] = $this->_latestFilters;
        $method['docComment'] = $this->_latestDocComment;
        
        $this->_methods[] = $method;
        $this->_nextName = null;
        $this->_resetLatests();
    }
    
    public function tokenCurlyClose()
    {
        $this->exitContext(array(
            'methods' => $this->_methods,
            'columns' => $this->_columns,
            'vars' => $this->_vars
        ));
    }
}
