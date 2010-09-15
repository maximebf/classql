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
 
namespace ClassQL;

use Parsec\Context as BaseContext;

class Context extends BaseContext
{
    protected $_latestModifiers = array();
    
    public function tokenStatic($value)
    {
        $this->_latestModifiers[] = $value;
    }
    
    public function tokenAbstract($value)
    {
        $this->_latestModifiers[] = $value;
    }
    
    public function tokenPrivate($value)
    {
        $this->_latestModifiers[] = $value;
    }
    
    public function tokenVirtual($value)
    {
        $this->_latestModifiers[] = $value;
    }
    
    protected function _resetModifiers()
    {
        $this->_latestModifiers = array();
    }
    
    public function tokenComment() {
        $this->enterContext('Comment');
    }
    
    public function tokenCommentOpen()
    {
        $this->enterContext('MultilineComment');
    }
    
    public function tokenEol()
    {
        
    }
    
    public function tokenWhitespace()
    {
        
    }
    
    public function __call($method, $args)
    {
        $this->_syntaxError(lcfirst(substr($method, 5)));
    }
    
    protected function _syntaxError($token)
    {
        throw new ParserException("Syntax error, unexpected token '$token' in context '" . get_class($this) . "'");
    }
}