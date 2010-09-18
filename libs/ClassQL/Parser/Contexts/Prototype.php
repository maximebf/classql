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

use ClassQL\Parser\Context,
    ClassQL\Parser\Exception;

class Prototype extends Context
{
    protected $_proto = array();
    
    protected $_nextStringType;
    
    public function tokenAs()
    {
        $this->_nextStringType = 'table';
    }
    
    public function tokenExtends()
    {
        $this->_nextStringType = 'extends';
    }
    
    public function tokenImplements()
    {
        $this->_nextStringType = 'implements';
    }
    
    public function tokenString($value)
    {
        $key = $this->_nextStringType;
        if (array_key_exists($key, $this->_proto)) {
            if ($this->_nextStringType != 'implements') {
                $this->_syntaxError('string');
            }
            if (!is_array($this->_proto[$key])) {
                $this->_proto[$key] = array($this->_proto[$key]);
            }
            $this->_proto[$key][] = $value;
        } else {
            $this->_proto[$key] = $value;
        }
    }
    
    public function tokenComma()
    {
        if ($this->_nextStringType != 'implements') {
            $this->_syntaxError('comma');
        }
    }
    
    public function tokenParenthOpen()
    {
        if (!empty($this->_proto)) {
            throw new Exception('Wrong prototype declaration for function');
        }
        
        $params = $this->enterContext('Parameters');
        $func = array_merge($this->_proto, $this->enterContext('Operation'));
        $func['type'] = 'function';
        $func['params'] = $params;
        $this->exitContext($func);
    }
    
    public function tokenCurlyOpen()
    {
        $model = array_merge($this->_proto, $this->enterContext('Model'));
        $model['type'] = 'model';
        $this->exitContext($model);
    }
}
