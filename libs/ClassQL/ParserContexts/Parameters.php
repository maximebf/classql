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
 
namespace ClassQL\ParserContexts;

use ClassQL\Context;

class Parameters extends Context
{
    protected $_vars = array();
    
    public function tokenVariable($value)
    {
        if (!empty($this->_args)) {
            $this->_syntaxError('variable');
        }
        
        $this->_vars[] = substr($value, 1);
    }
    
    public function tokenWildcard()
    {
        if (!empty($this->_args)) {
            $this->_syntaxError('wildcard');
        }
        
        $this->_vars[] = '*';
    }
    
    public function tokenComma()
    {
        $this->exitContext(array_merge($this->_vars, $this->enterContext('Parameters')));
    }
    
    public function tokenParenthClose()
    {
        $this->exitContext($this->_vars);
    }
}