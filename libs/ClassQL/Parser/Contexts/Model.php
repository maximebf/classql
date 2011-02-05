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

use ClassQL\Parser\ContainerContext,
    ClassQL\Parser\Exception;

class Model extends ContainerContext
{
    /** @var array */
    protected $_columns = array();
    
    /** @var array */
    protected $_vars = array();
    
    /** @var array */
    protected $_methods = array();
    
    /** @var string */
    protected $_nextName;
    
    public function tokenString($value)
    {
        if ($this->_nextName === null) {
            // first match on a token, means its an identifier for something
            // that has yet to be defined
            $this->_nextName = $value;
            return;
        }
        // second match on a string token means a column
        
        if (isset($this->_columns[$this->_nextName]) || isset($this->_vars[$this->_nextName])) {
            throw new Exception("Cannot redeclare '$this->_nextName'");
        }
        
        $this->_columns[$this->_nextName] = array(
            'name' => $this->_nextName,
            'type' => $value,
            'sql' => trim($this->_nextName . ' ' . $value . $this->enterContext('Line')),
            'docComment' => $this->_latestDocComment, 
            'attributes' => $this->_latestAttributes
        );
        
        $this->_nextName = null;
        $this->_resetLatests();
    }
    
    public function tokenVariable($value)
    {
        if (isset($this->_columns[substr($value, 1)]) || isset($this->_vars[$value])) {
            throw new Exception("Cannot redeclare '$value'");
        }
        
        if (!$this->getParser()->isNextToken('equal', array('whitespace'))) {
            $this->_syntaxError();
        }
        
        $this->getParser()->skipUntil('equal')->skipNext();
        
        $this->_vars[$value] = array_merge(
            $this->enterContext('Variable'),
            array('name' => $value)
        );
    }
    
    public function tokenParenthOpen()
    {
        if ($this->_nextName === null) {
            $this->_syntaxError('parenthOpen');
        }
        // parenthOpen after a string means a function
        
        if (isset($this->_methods[$this->_nextName])) {
            throw new Exception("Cannot redeclare '$this->_nextName()'");
        }
        
        // parse parameters (until the next parentClose)
        $params = $this->enterContext('Parameters');
        $modifiers = $this->_latestModifiers;
        
        if (!count(array_intersect(array('private', 'public', 'protected'), $modifiers))) {
            $modifiers[] = 'public';
        }
        
        $this->_methods[$this->_nextName] = array_merge(
            $this->enterContext('Operation'), // parses the function body
            array(
                'type' => 'method',
                'name' => $this->_nextName,
                'params' => $params,
                'modifiers' => $modifiers,
                'attributes' => $this->_latestAttributes,
                'docComment' => $this->_latestDocComment
            )
        );
        
        $this->_nextName = null;
        $this->_resetLatests();
    }
    
    public function tokenCurlyClose()
    {
        $this->exitContext(array(
            'columns' => $this->_columns,
            'vars' => $this->_vars,
            'methods' => $this->_methods
        ));
    }
    
    public function _resetLatests()
    {
        parent::_resetLatests();
        $this->_lastAttribute = null;
    }
}
