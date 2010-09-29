CREATE TABLE <?php echo $table ?> (
    <?php echo implode(",\n    ", array_map(function($v) { return $v['sql']; }, $columns)) ?>

);