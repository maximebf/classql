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

class Parameters extends Context
{
    /** @var string */
    protected $_paramName;
    
    /** @var string */
    protected $_paramValue;
    
    /** @var bool */
    protected $_hasDefaultValue = false;
    
    public function tokenVariable($value)
    {
        if (!empty($this->_paramName)) {
            // only one variable possible
            $this->_syntaxError('variable');
        }
        
        $this->_paramName = $value;
        $this->_paramValue = $value;
    }
    
    public function tokenEqual()
    {
        $this->_hasDefaultValue = true;
    }
    
    public function tokenValue($value)
    {
        if (!$this->_hasDefaultValue) {
            $this->_syntaxError('value');
        }
        $this->_paramValue .= " = $value";
    }
    
    public function tokenString($value)
    {
        if (!$this->_hasDefaultValue) {
            $this->_syntaxError('string');
        }
        $this->_paramValue .= " = $value";
    }
    
    public function tokenComma()
    {
        $this->exitContext(array_merge(
            array($this->_paramName => $this->_paramValue), 
            $this->enterContext('Parameters')
        ));
    }
    
    public function tokenParenthClose()
    {
        $this->exitContext(array($this->_paramName => $this->_paramValue));
    }
}
