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

use ClassQL\Parser\Context,
    ClassQL\Parser\Exception;

class ArrayContext extends Arguments
{
    /** @var string */
    protected $_key = null;
    
    public function tokenString($value)
    {
        if ($this->getParser()->isNextToken('arrayAssoc', array('whitespace'))) {
            $this->getParser()->skipUntil('arrayAssoc')->skipNext();
            $this->_key = $value;
        } else {
            parent::tokenString($value);
        }
    }
    
    public function tokenComma()
    {
        if ($this->_key !== null) {
            $this->_arg['key'] = $this->_key;
        }
        $this->exitContext(array_merge(array($this->_arg), $this->enterContext('ArrayContext')));
    }
    
    public function tokenArrayClose()
    {
        if ($this->_key !== null) {
            $this->_arg['key'] = $this->_key;
        }
        $this->exitContext(array($this->_arg));
    }
    
    public function tokenParenthClose()
    {
        $this->_syntaxError('parenthClose');
    }
}