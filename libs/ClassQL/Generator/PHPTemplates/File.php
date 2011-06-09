<?php echo '<?php'; ?>

/**
 * Generated by ClassQL
 */

<?php if ($namespace !== null) printf('namespace %s;', $namespace); ?>

<?php if (count($uses)): ?>
    use <?php echo implode(",\n    ", $uses); ?>;
<?php endif; ?>

<?php foreach ($objects as $object): ?>
    <?php echo $object; ?>
<?php endforeach; ?>