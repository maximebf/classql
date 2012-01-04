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
    protected $curlyCount = 1;
    
    /** @var array */
    protected $vars = array();
    
    /** @var array */
    protected $inlines = array();
    
    public function tokenVariable($value)
    {
        // catches variables from the sql string
        $this->vars[] = $value;
        $this->value .= $value;
    }
    
    public function tokenAtWord($value)
    {
        $args = array();
        if ($this->getParser()->isNextToken('parenthOpen')) {
            $this->getParser()->skipNext();
            $args = $this->enterContext('Arguments');
        }
        
        if ($this->getParser()->isNextToken('curlyOpen', array('whitespace'))) {
            $this->getParser()->skipUntil('curlyOpen');
            $args[] = array(
                'type' => 'sql',
                'value' => $this->enterContext('Block')
            );
        }
        
        $variable = '$func' . uniqid();
        $this->vars[] = $variable;
        $this->value .= $variable;
        
        $this->inlines[$variable] = array(
            'type' => 'function',
            'name' => substr($value, 1),
            'variable' => $variable,
            'args' => $args
        );
    }
    
    public function tokenExpression()
    {
        $expression = $this->enterContext('Expression');
        
        $variable = '$expr' . uniqid();
        $this->vars[] = $variable;
        $this->value .= $variable;
        
        $this->inlines[$variable] = array(
            'type' => 'expression',
            'variable' => $variable,
            'expression' => $expression
        );
    }
    
    public function tokenCurlyOpen()
    {
        // counts curly braces to avoid exciting to early
        $this->curlyCount++;
        $this->value .= '{';
    }
    
    public function tokenCurlyClose()
    {
        if ($this->curlyCount-- > 1) {
            $this->value .= '}';
            return;
        }
        
        $this->exitContext(array(
            'sql' => trim($this->value),
            'vars' => $this->vars,
            'inlines' => $this->inlines
        ));
    }
}
