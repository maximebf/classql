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
    protected $columns = array();
    
    /** @var array */
    protected $vars = array();
    
    /** @var array */
    protected $methods = array();
    
    /** @var string */
    protected $nextName;
    
    public function tokenString($value)
    {
        if ($this->nextName === null) {
            // first match on a token, means its an identifier for something
            // that has yet to be defined
            $this->nextName = $value;
            return;
        }
        // second match on a string token means a column
        
        if (isset($this->columns[$this->nextName]) || isset($this->vars[$this->nextName])) {
            throw new Exception("Cannot redeclare '$this->nextName'");
        }
        
        $this->columns[$this->nextName] = array(
            'name' => $this->nextName,
            'type' => $value,
            'sql' => trim($this->nextName . ' ' . $value . $this->enterContext('Line')),
            'docComment' => $this->latestDocComment, 
            'attributes' => $this->latestAttributes
        );
        
        $this->nextName = null;
        $this->resetLatests();
    }
    
    public function tokenVariable($value)
    {
        if (isset($this->columns[substr($value, 1)]) || isset($this->vars[$value])) {
            throw new Exception("Cannot redeclare '$value'");
        }
        
        if (!$this->getParser()->isNextToken('equal', array('whitespace'))) {
            $this->syntaxError();
        }
        
        $this->getParser()->skipUntil('equal');
        
        $this->vars[$value] = array_merge(
            $this->enterContext('Variable'),
            array('name' => $value)
        );
    }
    
    public function tokenParenthOpen()
    {
        if ($this->nextName === null) {
            $this->syntaxError('parenthOpen');
        }
        // parenthOpen after a string means a function
        
        if (isset($this->methods[$this->nextName])) {
            throw new Exception("Cannot redeclare '$this->nextName()'");
        }
        
        // parse parameters (until the next parentClose)
        $params = $this->enterContext('Parameters');
        $modifiers = $this->latestModifiers;
        
        if (!count(array_intersect(array('private', 'public', 'protected'), $modifiers))) {
            $modifiers[] = 'public';
        }
        
        $this->methods[$this->nextName] = array_merge(
            $this->enterContext('Operation'), // parses the function body
            array(
                'type' => 'method',
                'name' => $this->nextName,
                'params' => $params,
                'modifiers' => $modifiers,
                'attributes' => $this->latestAttributes,
                'docComment' => $this->latestDocComment
            )
        );
        
        $this->nextName = null;
        $this->resetLatests();
    }
    
    public function tokenCurlyClose()
    {
        $this->exitContext(array(
            'columns' => $this->columns,
            'vars' => $this->vars,
            'methods' => $this->methods
        ));
    }
    
    public function _resetLatests()
    {
        parent::_resetLatests();
        $this->lastAttribute = null;
    }
}
