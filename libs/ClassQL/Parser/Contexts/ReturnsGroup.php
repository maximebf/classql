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

use ClassQL\Parser\Context;

class ReturnsGroup extends Context
{
    /** @var array */
    protected $return;
    
    public function tokenNull()
    {
        $this->return = array('type' => 'null');
    }
    
    public function tokenWildcard()
    {
        $this->return = array('type' => 'object');
    }
    
    public function tokenString($value)
    {
        if (in_array($value, array('update', 'last_insert_id'))) {
            $this->return = array('type' => $value);
        } else {
            $property = $value;
            if ($this->getParser()->isNextToken('opreturns')) {
                $this->getParser()->skipNext();
                if (!$this->getParser()->isNextToken('string')) {
                    $this->syntaxError('opreturns');
                }
                $value = $this->getParser()->getNextTokenValue();
                $this->getParser()->skipNext();
            }
            
            $collection = false;
            if ($this->getParser()->isNextToken('arrayOpen')) {
                $collection = true;
                $this->getParser()->skipUntil('arrayClose');
            }
            
            if ($value === 'value') {
                $this->return = array(
                    'type' => $collection ? 'value_collection' : 'value',
                    'property' => $property
                );
            } else {
                $this->return = array(
                    'type' => $collection ? 'collection' : 'class',
                    'value' => $value,
                    'property' => $property
                );
            }
        }
    }
    
    public function tokenParenthOpen()
    {
        if (!empty($this->return)) {
            $this->syntaxError('parenthOpen');
        }
        $returns = $this->enterContext('ReturnsGroup');
        $this->return = $returns['returns'];
    }
    
    public function tokenParenthClose()
    {
        $this->exitContext(array('returns' => $this->return));
    }
    
    public function tokenWith()
    {
        $query = $this->enterContext('ReturnsWith');
        $this->return['with'] = $query['returns'];
        $query['returns'] = $this->return;
        $this->exitContext($query);
    }
}