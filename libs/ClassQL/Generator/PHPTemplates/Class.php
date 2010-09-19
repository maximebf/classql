<?php if (!empty($docComment)): ?>
/**<?php echo $docComment ?>
*/
<?php endif; ?>
<?php echo $this->_renderModifiers($modifiers); ?>
class <?php echo $name; ?> extends <?php echo $extends ?> {

<?php if (!in_array('virtual', $modifiers)): ?>
    /** 
     * @var string 
     */
    public $tableName = '<?php echo $table ?>';
    
<?php endif; ?>
<?php foreach ($columns as $column): ?>
    /**<?php if (!empty($column['docComment'])) echo "\n     " . trim($column['docComment']) ?> 
     * @var <?php echo $column['type'] ?> 
     */
    public $<?php echo $column['name'] ?>;
    
<?php endforeach; ?>
    protected function init()
    {
        parent::init();
<?php foreach ($vars as $var): ?>

<?php if ($var['type'] == 'sql'): ?>
        $this-><?php echo substr($var['name'], 1) ?> = <?php echo str_replace("\n", "\n    ", $this->_renderQueryInClass($var['value'])) ?>;
<?php else: ?>
        $this-><?php echo substr($var['name'], 1) ?> = <?php echo $this->_renderArray($var['value']) ?>;
<?php endif; ?>
<?php endforeach; ?>
    }

<?php foreach ($methods as $method): ?>
    <?php echo $method; ?>
    
<?php endforeach; ?>
}