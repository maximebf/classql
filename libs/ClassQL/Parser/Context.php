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

use Parsec\Context as BaseContext;

/**
 * Base context
 */
class Context extends BaseContext
{
    public function tokenComment()
    {
        $this->enterContext('Comment');
    }
    
    public function tokenCommentOpen()
    {
        $this->enterContext('MultilineComment');
    }
    
    public function tokenEol() {}
    
    public function tokenWhitespace() {}
    
    /**
     * Throws a syntax error for all undefined token

     * @param string $method
     * @param array $args
     */
    public function __call($method, $args)
    {
        $this->_syntaxError(lcfirst(substr($method, 5)));
    }
    
    /**
     * Throw a syntax error exception

     * @param string $token
     */
    protected function _syntaxError($token)
    {
        throw new Exception("Syntax error, unexpected token '$token' in context '" . get_class($this) . "'");
    }
}
