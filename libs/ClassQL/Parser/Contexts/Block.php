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

class Block extends CatchAllContext
{
    protected $_curlyCount = 1;
    
    protected $_vars = array();
    
    public function tokenVariable($value)
    {
        $this->_vars[] = $value;
        $this->_value .= $value;
    }
    
    public function tokenCurlyOpen()
    {
        $this->_curlyCount++;
        $this->_value .= '{';
    }
    
    public function tokenCurlyClose()
    {
        if ($this->_curlyCount-- > 1) {
            $this->_value .= '}';
            return;
        }
        
        $this->exitContext(array(
            'sql' => trim($this->_value),
            'vars' => $this->_vars
        ));
    }
}
