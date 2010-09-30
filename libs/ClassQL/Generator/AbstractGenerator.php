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
 
namespace ClassQL\Generator;

/**
 * Abstract class for generators
 */
abstract class AbstractGenerator implements Generator
{
    /** @var array */
    protected $_templates = array();
    
    /**
     * {@inheritDoc}
     */
    public function generate(array $descriptor)
    {
        return $this->_generateFile($descriptor);
    }
    
    /**
     * Generates a file
     * 
     * @param array $descriptor
     * @return string
     */
    abstract protected function _generateFile(array $descriptor);
    
    /**
     * Renders a template
     * 
     * @param string $template
     * @param array $vars
     * @return string
     */
    protected function _renderTemplate($template, array $vars = array())
    {
        extract($vars);
        ob_start();
        include $this->_templates[$template];
        return ob_get_clean();
    }
}
