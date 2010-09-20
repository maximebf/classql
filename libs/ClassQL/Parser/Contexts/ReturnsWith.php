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

class ReturnsWith extends ReturnsGroup
{
    /** @var array */
    protected $_returns = array();
    
    public function tokenWith()
    {
        $this->_checkReturns();
        $query = $this->enterContext('ReturnsWith');
        $this->_returns = array_merge($this->_returns, $query['returns']);
        $query['returns'] = $this->_returns;
        $this->exitContext($query);
    }
    
    public function tokenParenthClose()
    {
        $this->_checkReturns();
        $this->exitContext(array('returns' => $this->_returns));
    }
    
    public function tokenCurlyOpen()
    {
        $this->_checkReturns();
        $query = $this->enterContext('Block');
        $query['returns'] = $this->_returns;
        $this->exitContext($query);
    }
    
    protected function _checkReturns()
    {
        if (empty($this->_returns)) {
            if (empty($this->_return)) {
                $this->_syntaxError('with');
            }
            $this->_returns[] = $this->_return;
        }
    }
}