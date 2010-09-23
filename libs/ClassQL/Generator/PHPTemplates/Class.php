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
    public static $tableName = '<?php echo $table ?>';
    
<?php endif; ?>
<?php foreach ($vars as $var): ?>
<?php if ($var['type'] == 'sql'): ?>
    /**
     * @var string
     */
    public static $<?php echo substr($var['name'], 1) ?> = '<?php echo str_replace("'", "\'", $var['value']) ?>';
<?php else: ?>
    /**
     * @var array
     */
    public static $<?php echo substr($var['name'], 1) ?> = <?php echo $this->_renderArray($var['value']) ?>;
<?php endif; ?>

<?php endforeach; ?>
<?php foreach ($columns as $column): ?>
    /**<?php if (!empty($column['docComment'])) echo "\n     " . trim($column['docComment']) ?> 
     * @var <?php echo $column['type'] ?> 
     */
    public $<?php echo $column['name'] ?>;
    
<?php endforeach; ?>

<?php foreach ($methods as $method): ?>
    <?php echo $method; ?>
    
<?php endforeach; ?>
}