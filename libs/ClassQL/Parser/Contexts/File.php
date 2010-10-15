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

use ClassQL\Parser\ContainerContext;

class File extends ContainerContext
{
    /** @var string */
    protected $_namespace;
    
    /** @var array */
    protected $_uses = array();
    
    /** @var array */
    protected $_objects = array();
    
    public function tokenNamespace()
    {
        $this->_namespace = trim($this->enterContext('Line'));
    }
    
    public function tokenUse()
    {
        $this->_uses = array_merge($this->_uses, $this->enterContext('UseDeclaration'));
    }
    
    public function tokenString($value)
    {
        // a string means an identifier for a class or a function
        
        if (isset($this->_objects[$value])) {
            throw new Exception("Cannot redeclare '$value()'");
        }
        
        $this->_objects[$value] = array_merge(
            $this->enterContext('Prototype'),
            array(
                'name' => $value,
                'namespace' => $this->_namespace,
                'modifiers' => $this->_latestModifiers,
                'attributes' => $this->_latestAttributes,
                'docComment' => $this->_latestDocComment
            )
        );
        
        $this->_resetLatests();
    }
    
    public function tokenEos()
    {
        $this->exitContext(array(
            'namespace' => $this->_namespace,
            'uses' => $this->_uses,
            'objects' => $this->_objects
        ));
    }
}
