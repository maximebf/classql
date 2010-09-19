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

class Operation extends Context
{
    /** @var mixed */
    protected $_returns = false;
    
    public function tokenReturns()
    {
        // the returns token has been used
        $this->_returns = true;
    }
    
    public function tokenString($value)
    {
        if ($this->_returns === false) {
            // no returns token before string token
            $this->_syntaxError('string');
        }
        $this->_returns = $value;
    }
    
    public function tokenCurlyOpen()
    {
        if ($this->_returns === true) {
            // returns token but no string token
            $this->_syntaxError('curlyOpen');
        }
        
        $query = $this->enterContext('Block');
        if ($this->_returns !== false) {
            $query['returns'] = $this->_returns;
        }
        $this->exitContext(array('query' => $query));
    }
    
    public function tokenPointer()
    {
        if ($this->_returns === true) {
            // returns token but no string token
            $this->_syntaxError('curlyOpen');
        }
        
        $this->exitContext(array(
            'callback' => $this->enterContext('Callback')
        ));
    }
}
