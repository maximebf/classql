<?php if (!empty($docComment)): ?>
/**<?php echo $docComment ?>
*/
<?php endif; ?>
<?php if (!empty($modifiers)) echo implode(' ', $modifiers) . ' '; ?>
class <?php echo $name; ?> extends <?php echo $extends ?> {
<?php foreach ($methods as $method): ?>

    <?php echo $method; ?>
<?php endforeach; ?>

}