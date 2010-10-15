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
 * Generates models as sql code
 */
class SQLGenerator extends AbstractGenerator
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_templates =  array(
            'create' => __DIR__ . '/SQLTemplates/CreateTable.php',
            'drop' => __DIR__ . '/SQLTemplates/DropTable.php'
        );
    }
    
    /**
     * Generates DROP TABLE statements
     * 
     * @param array $ast
     * @return string
     */
    public function generateDrop($ast)
    {
        return $this->_generateFile($ast, 'drop');
    }
    
    /**
     * {@inheritDoc}
     */
    public function _generateFile(array $ast, $template = 'create')
    {
        $sql = '';
        foreach ($ast['objects'] as $object) {
            if ($object['type'] == 'class') {
                $sql .= $this->_generateTable($object, $template);
            }
        }
        return $sql;
    }
    
    /**
     * Generates sql code for a table
     * 
     * @param array $ast
     * @param string $template
     */
    protected function _generateTable($ast, $template = 'create')
    {
        return $this->_renderTemplate($template, $ast);
    }
}
