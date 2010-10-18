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

class Arguments extends Context
{
    /** @var array */
    protected $_arg;
    
    public function tokenValue($value)
    {
        if (!empty($this->_arg)) {
            // only one token possible
            $this->_syntaxError();
        }
        
        $this->_arg = array(
            'type' => 'scalar', 
            'value' => str_replace('\\"', '"', trim($value, '"'))
        );
    }
    
    public function tokenString($value)
    {
        if (!empty($this->_arg)) {
            // only one token possible
            $this->_syntaxError();
        }
        
        $this->_arg = array(
            'type' => 'identifier', 
            'value' => $value
        );
    }
    
    public function tokenVariable($value)
    {
        if (!empty($this->_arg)) {
            // only one token possible
            $this->_syntaxError();
        }
        
        $this->_arg = array(
            'type' => 'variable', 
            'value' => $value
        );
    }
    
    public function tokenBoolean($value)
    {
        if (!empty($this->_arg)) {
            // only one token possible
            $this->_syntaxError();
        }
        
        $this->_arg = array(
            'type' => 'boolean', 
            'value' => $value
        );
    }
    
    public function tokenCurlyOpen()
    {
        if (!empty($this->_arg)) {
            // only one token possible
            $this->_syntaxError();
        }
        
        $this->_arg = array(
            'type' => 'sql', 
            'value' => $this->enterContext('Block')
        );
    }
    
    public function tokenArrayOpen()
    {
        if (!empty($this->_arg)) {
            // only one token possible
            $this->_syntaxError();
        }
        
        $this->_arg = array(
            'type' => 'array',
            'value' => $this->enterContext('ArrayContext')
        );
    }
    
    public function tokenComma()
    {
        $this->exitContext(array_merge(
            array($this->_arg), 
            $this->enterContext('Arguments')
        ));
    }
    
    public function tokenParenthClose()
    {
        $this->exitContext(array($this->_arg));
    }
}
