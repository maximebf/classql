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
    ClassQL\Generator\SQLGenerator;

class Generate extends CLI
{
    public function executeAst($args)
    {
        $ast = Session::getParser()->parseFile($args[0]);
        var_dump($ast);
    }
    
    public function executePhp($args)
    {
        $ast = Session::getParser()->parseFile($args[0]);
        $generator = new PHPGenerator();
        $this->println($generator->generate($ast));
    }
    
    public function executeSqlCreate($args)
    {
        $ast = Session::getParser()->parseFile($args[0]);
        $generator = new SQLGenerator();
        $this->println($generator->generate($ast));
    }
    
    public function executeSqlDrop($args)
    {
        $ast = Session::getParser()->parseFile($args[0]);
        $generator = new SQLGenerator();
        $this->println($generator->generateDrop($ast));
    }
}