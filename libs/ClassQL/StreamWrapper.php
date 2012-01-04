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
 
namespace ClassQL;

use MetaP\CacheableStreamWrapper;

/**
 * Allows to loads models using the classql:// protocol
 */
class StreamWrapper extends CacheableStreamWrapper
{
    /**
     * Registers the current stream wrapper with the specified protocol
     *
     * @param string $protocol
     */
    public static function register()
    {
        stream_wrapper_register('classql', 'ClassQL\StreamWrapper');
    }
    
    /**
     * {@inheritDoc}
     */
    public function buildCache($filename)
    {
        $parser = Session::getParser();
        $generator = Session::getGenerator();
        $ast = $parser->parseFile($filename);
        $this->content = $generator->generate($ast);
        return true;
    }
}