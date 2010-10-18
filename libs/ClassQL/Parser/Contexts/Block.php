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

use ClassQL\Parser\CatchAllContext;

class Block extends CatchAllContext
{
    /** @var int */
    protected $_curlyCount = 1;
    
    /** @var array */
    protected $_vars = array();
    
    /** @var array */
    protected $_functions = array();
    
    public function tokenVariable($value)
    {
        // catches variables from the sql string
        $this->_vars[] = $value;
        $this->_value .= $value;
    }
    
    public function tokenAttribute($value)
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
        
        $variable = '$deco' . uniqid();
        $this->_vars[] = $variable;
        $this->_value .= $variable;
        
        $this->_functions[$variable] = array(
            'name' => substr($value, 1),
            'variable' => $variable,
            'args' => $args
        );
    }
    
    public function tokenCurlyOpen()
    {
        // counts curly braces to avoid exciting to early
        $this->_curlyCount++;
        $this->_value .= '{';
    }
    
    public function tokenCurlyClose()
    {
        if ($this->_curlyCount-- > 1) {
            $this->_value .= '}';
            return;
        }
        
        $this->exitContext(array(
            'sql' => trim($this->_value),
            'vars' => $this->_vars,
            'functions' => $this->_functions
        ));
    }
}
