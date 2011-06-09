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

use ClassQL\Parser\Context;

class Arguments extends Context
{
    /** @var array */
    protected $_arg;
    
    public function tokenValue($value)
    {
        if (!empty($this->_arg)) {
            // only one token possible
            $this->_syntaxError();
        }
        
        $this->_arg = array(
            'type' => 'scalar', 
            'value' => $value
        );
    }
    
    public function tokenString($value)
    {
        if (!empty($this->_arg)) {
            // only one token possible
            $this->_syntaxError();
        }
        
        $this->_arg = array(
            'type' => 'identifier', 
            'value' => $value
        );
    }
    
    public function tokenVariable($value)
    {
        if (!empty($this->_arg)) {
            // only one token possible
            $this->_syntaxError();
        }
        
        $this->_arg = array(
            'type' => 'variable', 
            'value' => $value
        );
    }
    
    public function tokenCallback($value)
    {
        if (!empty($this->_arg)) {
            // only one token possible
            $this->_syntaxError();
        }
        
        $this->_arg = array(
            'type' => 'callback', 
            'value' => $value
        );
    }
    
    public function tokenBoolean($value)
    {
        if (!empty($this->_arg)) {
            // only one token possible
            $this->_syntaxError();
        }
        
        $this->_arg = array(
            'type' => 'boolean', 
            'value' => $value
        );
    }
    
    public function tokenNull($value)
    {
        if (!empty($this->_arg)) {
            // only one token possible
            $this->_syntaxError();
        }
        
        $this->_arg = array(
            'type' => 'null',
            'value' => $value
        );
    }
    
    public function tokenAtWord($value)
    {
        $args = array();
        if ($this->getParser()->isNextToken('parenthOpen')) {
            $this->getParser()->skipNext();
            $args = $this->enterContext('Arguments');
        }
        
        if ($this->getParser()->isNextToken('curlyOpen', array('whitespace'))) {
            $this->getParser()->skipUntil('curlyOpen')->skipNext();
            $args[] = array(
                'type' => 'sql',
                'value' => $this->enterContext('Block')
            );
        }
        
        $this->_arg = array(
            'type' => 'function', 
            'value' => array(
                'name' => substr($value, 1),
                'args' => $args
            )
        );
    }
    
    public function tokenExpression()
    {
        if (!empty($this->_arg)) {
            // only one token possible
            $this->_syntaxError();
        }
        
        $this->_arg = array(
            'type' => 'expression', 
            'value' => $this->enterContext('Expression')
        );
    }
    
    public function tokenCurlyOpen()
    {
        if (!empty($this->_arg)) {
            // only one token possible
            $this->_syntaxError();
        }
        
        $this->_arg = array(
            'type' => 'sql', 
            'value' => $this->enterContext('Block')
        );
    }
    
    public function tokenArrayOpen()
    {
        if (!empty($this->_arg)) {
            // only one token possible
            $this->_syntaxError();
        }
        
        $this->_arg = array(
            'type' => 'array',
            'value' => $this->enterContext('ArrayContext')
        );
    }
    
    public function tokenComma()
    {
        $this->exitContext(array_merge(
            array($this->_arg), 
            $this->enterContext('Arguments')
        ));
    }
    
    public function tokenParenthClose()
    {
        $this->exitContext(array($this->_arg));
    }
}
