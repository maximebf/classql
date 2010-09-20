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

class ReturnsGroup extends Context
{
    /** @var array */
    protected $_return;
    
    public function tokenNull()
    {
        $this->_return = array('type' => 'null');
    }
    
    public function tokenReturnsValue()
    {
        $this->_return = array('type' => 'value');
    }
    
    public function tokenWildcard()
    {
        $this->_return = array('type' => 'object');
    }
    
    public function tokenString($value)
    {
        $this->_return = array(
            'type' => 'class',
            'value' => $value
        );
    }
    
    public function tokenArrayOpen()
    {
        if (empty($this->_return) || $this->_return['type'] !== 'class') {
            $this->_syntaxError('arrayOpen');
        }
    }
    
    public function tokenArrayClose()
    {
        $this->_return['type'] = 'collection';
    }
    
    public function tokenParenthOpen()
    {
        if (!empty($this->_return)) {
            $this->_syntaxError('parenthOpen');
        }
        $returns = $this->enterContext('ReturnsGroup');
        $this->_return = $returns['returns'];
    }
    
    public function tokenParenthClose()
    {
        $this->exitContext(array('returns' => $this->_return));
    }
    
    public function tokenWith()
    {
        $query = $this->enterContext('ReturnsWith');
        $this->_return['with'] = $query['returns'];
        $query['returns'] = $this->_return;
        $this->exitContext($query);
    }
}