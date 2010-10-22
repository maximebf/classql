// ----------------------------------------------------------------------------
// Functions for '<?php echo $name ?>'

<?php if (!empty($docComment)): ?>
/**<?php echo $docComment ?>
*/
<?php endif; ?>
<?php echo $this->_renderModifiers($modifiers); ?>
function <?php echo $name; ?>(<?php echo implode(', ', $params); ?>) {
<?php if (isset($query)): ?>
    $stmt = <?php echo $this->_renderScope($type, $modifiers) . $execute_func_name; ?>(<?php echo implode(', ', array_keys($params)); ?>);
<?php if ($query['returns']['type'] != 'null'): ?>
<?php if ($query['returns']['type'] == 'collection'): ?>
    $data = $stmt->fetchAll(\PDO::FETCH_CLASS, '<?php echo $query['returns']['value'] ?>');
<?php elseif ($query['returns']['type'] == 'class'): ?>
    $stmt->setFetchMode(\PDO::FETCH_CLASS, '<?php echo $query['returns']['value'] ?>');
    $data = $stmt->fetch();
    $stmt->closeCursor();
<?php elseif ($query['returns']['type'] == 'value'): ?>
    $data = $stmt->fetchColumn();
<?php elseif ($query['returns']['type'] == 'last_insert_id'): ?>
    $this->id = \ClassQL\Session::getConnection()->lastInsertId();
<?php elseif ($query['returns']['type'] == 'update'): ?>
    if (($data = $stmt->fetch(\PDO::FETCH_ASSOC)) !== false) {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }
    $stmt->closeCursor();
<?php endif; ?>
<?php endif; ?>
<?php else: ?>
    $data = <?php echo $callback['name'] ?>(<?php echo $this->_renderArgs($callback['args'], array_keys($params)) ?>);
<?php endif; ?>
<?php if (!isset($query) || in_array($query['returns']['type'], array('value', 'class', 'collection'))):?>
    return $data;
<?php endif; ?>
}
<?php if (isset($query)): ?>

/**
 * Creates and executes the statement associated to {@see <?php echo $name ?>()} 
 * @return PDOStatement
 */
<?php echo $this->_renderModifiers($modifiers); ?>
function <?php echo $execute_func_name; ?>(<?php echo implode(', ', $params); ?>) {
    $sqlString = <?php echo $this->_renderScope($type, $modifiers) . $query_func_name; ?>(<?php echo implode(', ', array_keys($params)); ?>);
    $stmt = \ClassQL\Session::getConnection()->prepare($sqlString->sql);
    $stmt->execute($sqlString->params);
    return $stmt;
}

/**
 * Generates the query associated to {@see <?php echo $name ?>()} 
 * @return \ClassQL\SqlString
 */
<?php echo $this->_renderModifiers($modifiers); ?>
function <?php echo $query_func_name; ?>(<?php echo implode(', ', $params); ?>) {
<?php foreach ($query['inlines'] as $inline): ?>
<?php if ($inline['type'] == 'function'): ?>
    <?php echo $inline['variable'] ?> =  new \ClassQL\SqlString(<?php echo $this->_renderInlineFunc($inline, $params, $class) ?>);
<?php elseif ($inline['type'] == 'expression'): ?>
    <?php echo $inline['variable'] . ' = ' . $inline['expression'] ?>;
<?php endif; ?>
<?php endforeach; ?>
    $sql = "<?php echo $this->_renderQuery($query); ?>";
    $params = <?php echo $this->_renderQueryParams($query, array_keys($params)) ?>;
    return new \ClassQL\SqlString($sql, $params);
}
<?php endif; ?>