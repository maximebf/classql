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
    \DirectoryIterator,
    ClassQL\Session,
    ClassQL\Generator\PHPGenerator;

class StreamCache extends CLI
{
    /**
     * {@inheritDoc}
     */
    public function execute(array $args, array $options = array())
    {
        if (!\ClassQL\StreamCache::isEnabled()) {
            $this->println('StreamCache must be enabled');
            return;
        }
        parent::execute($args, $options);
    }
    
    public function executeCompile($args)
    {
        foreach ($args as $filename) {
            $this->compile($filename);
        }
    }
    
    public function executeClear($args)
    {
        \ClassQL\StreamWrapper::flushCache();
    }
    
    protected function compile($filename)
    {
        if (is_dir($filename)) {
            foreach (new DirectoryIterator($filename) as $file) {
                if (substr($file->getFilename(), 0, 1) !== '.') {
                    $this->compile($file->getPathname());
                }
            }
            return;
        }
        
        if (\ClassQL\StreamCache::has($filename)) {
            return;
        }
        
        $ast = Session::getParser()->parseFile($filename);
        $generator = new PHPGenerator();
        \ClassQL\StreamCache::set($filename, $generator->generate($ast));
    }
}