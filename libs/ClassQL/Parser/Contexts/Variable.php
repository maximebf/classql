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

use ClassQL\Parser\CatchAllContext;

class Variable extends CatchAllContext
{
    /** @var array */
    protected $_vars = array();
    
    /** @var mixed */
    protected $_array = false;
    
    public function tokenArrayOpen()
    {
        if (trim($this->_value) != '') {
            return;
        }
        
        $this->_array = array(
            'type' => 'array',
            'value' => $this->enterContext('ArrayContext')
        );
    }
    
    public function tokenVariable($value)
    {
        // catches variables from the sql string
        $this->_vars[] = $value;
        $this->_value .= $value;
    }
    
    public function tokenEol()
    {
        if ($this->_array !== false) {
            $this->exitContext($this->_array);
        }
    }
    
    public function tokenSemiColon()
    {
        if ($this->_array !== false) {
            $this->exitContext($this->_array);
        } else {
            $this->exitContext(array(
                'type' => 'sql',
                'value' => array(
                    'sql' => trim($this->_value),
                    'vars' => $this->_vars
                )
            ));
        }
    }
}
