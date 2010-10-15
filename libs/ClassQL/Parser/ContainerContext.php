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

/**
 * Context with support for modifiers and attributes
 */
class ContainerContext extends Context
{
    /** @var array */
    protected $_latestModifiers = array();
    
    /** @var array */
    protected $_latestAttributes = array();
    
    /** @var array */
    protected $_latestDocComment;
    
    public function tokenModifier($value)
    {
        $this->_latestModifiers[] = $value;
    }
    
    public function tokenAttribute($value)
    {
        $args = array();
        if ($this->getParser()->isNextToken('parenthOpen', array('whitespace'))) {
            $this->getParser()->skipUntil('parenthOpen')->skipNext();
            $args = $this->enterContext('Arguments');
        }
        
        $this->_latestAttributes[] = array(
            'name' => substr($value, 1),
            'args' => $args
        );
    }
    
    public function tokenDocCommentOpen()
    {
        $this->_latestDocComment = $this->enterContext('MultilineComment');
    }
    
    /**
     * Resets all latest arrays
     */
    protected function _resetLatests()
    {
        $this->_latestModifiers = array();
        $this->_latestAttributes = array();
        $this->_latestDocComment = null;
    }
}
