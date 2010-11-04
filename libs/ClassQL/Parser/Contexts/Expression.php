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

class Expression extends CatchAllContext
{
    /** @var int */
    protected $_parenthCount = 1;
    
    public function tokenVariable($value)
    {
        $this->_value .= str_replace(array('[', ']'), array("['", "']"), $value);
    }
    
    public function tokenArrayOpen()
    {
        $this->_value .= $this->enterContext('RewriteArray');
    }
    
    public function tokenParenthOpen()
    {
        $this->_parenthCount++;
        $this->_value .= '(';
    }
    
    public function tokenParenthClose()
    {
        if ($this->_parenthCount-- > 1) {
            $this->_value .= ')';
            return;
        }
        
        $this->exitContext($this->_value);
    }
}
