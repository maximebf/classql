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
    ClassQL\Parser\Parser,
    ClassQL\Generator\PHPGenerator,
    ClassQL\Generator\SQLGenerator;

class Generate extends CLI
{
    /** @var Parser */
    protected $_parser;
    
    public function __construct()
    {
        $this->_parser = new Parser();
    }
    
    public function executePhp($args)
    {
        $descriptor = $this->_parser->parseFile($args[0]);
        $generator = new PHPGenerator();
        $this->println($generator->generate($descriptor));
    }
    
    public function executeSql($args)
    {
        $descriptor = $this->_parser->parseFile($args[0]);
        $generator = new SQLGenerator();
        $this->println($generator->generate($descriptor));
    }
}