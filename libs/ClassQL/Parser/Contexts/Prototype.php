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
    /** @var array */
    protected $proto = array();
    
    /** @var string */
    protected $nextStringType;
    
    public function tokenAs()
    {
        $this->nextStringType = 'table';
    }
    
    public function tokenExtends()
    {
        $this->nextStringType = 'extends';
    }
    
    public function tokenImplements()
    {
        $this->nextStringType = 'implements';
    }
    
    public function tokenReturns()
    {
        $this->nextStringType = 'returns';
    }
    
    public function tokenString($value)
    {
        $key = $this->nextStringType;
        if (array_key_exists($key, $this->proto)) {
            if ($this->nextStringType != 'implements') {
                // only implements can have multiple values
                $this->syntaxError('string');
            }
            if (!is_array($this->proto[$key])) {
                $this->proto[$key] = array($this->proto[$key]);
            }
            $this->proto[$key][] = $value;
        } else {
            $this->proto[$key] = $value;
        }
    }
    
    public function tokenComma()
    {
        if ($this->nextStringType != 'implements') {
            $this->syntaxError('comma');
        }
    }
    
    public function tokenParenthOpen()
    {
        if (!empty($this->proto)) {
            throw new Exception('Wrong prototype declaration for function');
        }
        // parenthOpen means it's a function
        
        $params = $this->enterContext('Parameters');
        $func = array_merge(
            $this->proto, 
            $this->enterContext('Operation'),
            array(
                'type' => 'function',
                'params' => $params
            )
        );
        $this->exitContext($func);
    }
    
    public function tokenCurlyOpen()
    {
        // curlyOpen means it's a class
        $model = array_merge($this->proto, $this->enterContext('Model'));
        $model['type'] = 'class';
        $this->exitContext($model);
    }
}
