<?php echo '<?php' ?>

namespace <?php echo $namespace ?>;

use PSQL\Model;

<?php foreach ($models as $model): ?>
class <?php echo $model['name'] ?> extends Model
{
    protected $_fields = array(
        <?php foreach ($model['columns'] as $name => $column): ?>
        '<?php echo $name ?>' => <?php var_export($column) ?>,
        <?php endforeach; ?>
    );
    
<?php foreach ($model['methods'] as $method): ?>
    public <?php echo implode(' ', $method['modifiers']) ?> function <?php echo $method['name'] ?>()
    {
<?php if (isset($method['query'])): ?>
        $stmt = $this-><?php echo $method['name'] ?>_statement();
        return $stmt->execute($params);
<?php else: ?>
        return <?php echo $method['callback']['name'] ?>();
<?php endif; ?>
    }
    
<?php if (isset($method['query'])): ?>
    public <?php echo implode(' ', $method['modifiers']) ?> function <?php echo $method['name'] ?>_statement()
    {
        return $this->_connection->prepare("<?php echo $method['query']['sql'] ?>");
    }
    
<?php endif; endforeach; ?>
}

<?php endforeach; ?>
