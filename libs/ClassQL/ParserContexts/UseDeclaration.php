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

class UseDeclaration extends Context
{
    protected $_uses = array();
    
    public function tokenString($value)
    {
        if (!empty($this->_uses)) {
            $this->_syntaxError('string');
        }
        $this->_uses[] = $value;
    }
    
    public function tokenComma()
    {
        $this->_uses = array_merge($this->_uses, $this->enterContext('UseDeclaration'));
        $this->exitContext($this->_uses);
    }
    
    public function tokenSemiColon()
    {
        $this->exitContext($this->_uses);
    }
}
