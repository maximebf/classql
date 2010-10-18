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

use Parsec\Lexer as BaseLexer;

class Lexer extends BaseLexer
{
    public function __construct()
    {
        parent::__construct(array(
            'parenthOpen' => '\(',
            'parenthClose' => '\)',
            'curlyOpen' => '\{',
            'curlyClose' => '\}',
            'arrayOpen' => '\[',
            'arrayClose' => '\]',
            'arrayAssoc' => '=>',
            'comment' => '\/\/',
            'docCommentOpen' => '\/\*\*',
            'commentOpen' => '\/\*',
            'commentClose' => '\*\/',
            'modifier' => "\b(static|abstract|public|private|protected|virtual)\b",
            'namespace' => "\bnamespace\b",
            'use' => "\buse\b",
            'as' => "\brepresents\b",
            'extends' => "\bextends\b",
            'implements' => "\bimplements\b",
            'returns' => '\breturns\b',
            'opreturns' => ':',
            'wildcard' => '\*',
            'with' => '\+',
            'variable' => '\${1,2}[a-zA-Z_][a-z0-9A-Z_]*(\[[a-zA-Z0-9_]+\])*',
            'equal' => '=',
            'semiColon' => ';',
            'eol' => "\n",
            'pointer' => '\-\>',
            'comma' => ',',
            'callback' => '[a-zA-Z0-9_\\\]+::[a-zA-Z0-9_]+',
            'attribute' => '\@[a-zA-Z0-9_\\\]+(::[a-zA-Z0-9_]+)?',
            'value' => '("((?:[^\\\]*?(?:\\\")?)*?)"|[0-9]+)',
            'boolean' => '(true|false)',
            'string' => '[a-zA-Z0-9_\\\]+',
            'whitespace' => "[\t\s]+"
        ));
    }
}
