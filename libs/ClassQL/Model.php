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

class Model
{
    public function __construct(array $data = array())
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }
    
    public function save()
    {
        if (!property_exists(get_class(), 'primaryKey')) {
            throw new Exception("Missing static property 'primaryKey' for 'ClassQL\Model::save()'");
        }
        
        if (empty($this->{self::$primaryKey})) {
            if (!method_exists($this, 'insert')) {
                throw new Exception("Missing method 'insert' for 'ClassQL\Model::save()'");
            }
            $this->insert();
        } else {
            if (!method_exists($this, 'update')) {
                throw new Exception("Missing method 'update' for 'ClassQL\Model::save()'");
            }
            $this->update();
        }
    }
}
