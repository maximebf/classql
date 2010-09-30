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
 
namespace ClassQL\Parser;

/**
 * Context with support for modifiers and filters
 */
class ContainerContext extends Context
{
    /** @var array */
    protected $_latestModifiers = array();
    
    /** @var array */
    protected $_latestFilters = array();
    
    /** @var array */
    protected $_latestDocComment;
    
    public function tokenStatic($value)
    {
        $this->_latestModifiers[] = $value;
    }
    
    public function tokenAbstract($value)
    {
        $this->_latestModifiers[] = $value;
    }
    
    public function tokenPublic($value)
    {
        $this->_latestModifiers[] = $value;
    }
    
    public function tokenPrivate($value)
    {
        $this->_latestModifiers[] = $value;
    }
    
    public function tokenProtected($value)
    {
        $this->_latestModifiers[] = $value;
    }
    
    public function tokenVirtual($value)
    {
        $this->_latestModifiers[] = $value;
    }
    
    public function tokenFilter($value)
    {
        $this->_latestFilters[] = array(
            'name' => substr($value, 1),
            'args' => $this->enterContext('Filter')
        );
    }
    
    public function tokenDocCommentOpen()
    {
        $this->_latestDocComment = $this->enterContext('MultilineComment');
    }
    
    /**
     * Resets all latest arrays
     */
    protected function _resetLatests()
    {
        $this->_latestModifiers = array();
        $this->_latestFilters = array();
        $this->_latestDocComment = null;
    }
}
