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
 
namespace ClassQL\CLI;

use ClassQL\CLI,
    ClassQL\Session,
    ClassQL\Generator\PHPGenerator,
    ClassQL\Generator\SQLGenerator,
    DirectoryIterator;

class Schema extends CLI
{
    public function executeCreate($args)
    {
        $this->_generateAndExecuteSql($args);
    }
    
    public function executeDrop($args)
    {
        $this->_generateAndExecuteSql($args, 'generateDrop');
    }
    
    protected function _generateAndExecuteSql($filename, $method = 'generate')
    {
        if (is_array($filename)) {
            foreach ($filename as $file) {
                $this->_generateAndExecuteSql($file, $method);
            }
            return;
        }
        
        if (is_dir($filename)) {
            foreach (new DirectoryIterator($filename) as $file) {
                if (substr($file->getFilename(), 0, 1) !== '.') {
                    $this->_generateAndExecuteSql($file->getPathname(), $method);
                }
            }
            return;
        }
        
        $ast = Session::getParser()->parseFile($filename);
        $generator = new SQLGenerator();
        Session::getConnection()->exec($generator->$method($ast));
    }
}