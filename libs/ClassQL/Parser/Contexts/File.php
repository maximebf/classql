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
    protected $namespace;
    
    /** @var array */
    protected $uses = array();
    
    /** @var array */
    protected $objects = array();
    
    public function tokenNamespace()
    {
        $this->namespace = trim($this->enterContext('Line'));
    }
    
    public function tokenUse()
    {
        $this->uses = array_merge($this->uses, $this->enterContext('UseDeclaration'));
    }
    
    public function tokenString($value)
    {
        // a string means an identifier for a class or a function
        
        if (isset($this->objects[$value])) {
            throw new Exception("Cannot redeclare '$value()'");
        }
        
        $this->objects[$value] = array_merge(
            $this->enterContext('Prototype'),
            array(
                'name' => $value,
                'namespace' => $this->namespace,
                'modifiers' => $this->latestModifiers,
                'attributes' => $this->latestAttributes,
                'docComment' => $this->latestDocComment
            )
        );
        
        $this->resetLatests();
    }
    
    public function tokenEos()
    {
        $this->exitContext(array(
            'namespace' => $this->namespace,
            'uses' => $this->uses,
            'objects' => $this->objects
        ));
    }
}
