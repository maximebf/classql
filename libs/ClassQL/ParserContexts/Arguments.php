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

class Arguments extends Context
{
    protected $_args = array();
    
    public function tokenValue($value)
    {
        if (!empty($this->_args)) {
            $this->_syntaxError('value');
        }
        
        $this->_args[] = array(
            'type' => 'scalar', 
            'value' => str_replace('\\"', '"', trim($value, '"'))
        );
    }
    
    public function tokenString($value)
    {
        if (!empty($this->_args)) {
            $this->_syntaxError('string');
        }
        
        $this->_args[] = array(
            'type' => 'identifier', 
            'value' => $value
        );
    }
    
    public function tokenVariable($value)
    {
        if (!empty($this->_args)) {
            $this->_syntaxError('variable');
        }
        
        $this->_args[] = array(
            'type' => 'variable', 
            'value' => substr($value, 1)
        );
    }
    
    public function tokenComma()
    {
        $this->exitContext(array_merge($this->_args, $this->enterContext('Arguments')));
    }
    
    public function tokenParenthClose()
    {
        $this->exitContext($this->_args);
    }
}