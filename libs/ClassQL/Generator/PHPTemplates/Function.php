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
<?php endif; ?>
    $stmt->closeCursor();
<?php endif; ?>
<?php else: ?>
    $data = <?php echo $callback['name'] ?>(<?php echo $this->_renderArgs($callback['args'], array_keys($params)) ?>);
<?php endif; ?>
<?php if (!isset($query) || in_array($query['returns']['type'], array('value', 'class', 'collection'))):?>
<?php if (!empty($filters)): ?>
    return <?php echo $this->_renderScope($type, $modifiers) . $filter_func_name; ?>(new ArratIterator($data));
<?php else: ?>
    return $data;
<?php endif; ?>
<?php endif; ?>
}
<?php if (isset($query)): ?>

/**
 * Creates and executes the statement associated to {@see <?php echo $name ?>()} 
 * @return PDOStatement
 */
<?php echo $this->_renderModifiers($modifiers); ?>
function <?php echo $execute_func_name; ?>(<?php echo implode(', ', $params); ?>) {
    $stmt = <?php echo $this->_renderScope($type, $modifiers) . $statement_func_name ?>();
    $params = array(<?php echo $this->_renderQueryParams($query['vars'], array_keys($params)) ?>);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Creates the statement associated to {@see <?php echo $name ?>()} 
 * @return PDOStatement
 */
<?php echo $this->_renderModifiers($modifiers); ?>
function <?php echo $statement_func_name; ?>() {
    $query = '<?php echo $this->_renderQuery($query); ?>';
    return \ClassQL\Session::getConnection()->prepare($query);
}
<?php endif; ?>
<?php if (!empty($filters)): ?>

/**
 * Filters the fetched data for {@see <?php echo $name ?>()} 
 * @return array
 */
<?php echo $this->_renderModifiers($modifiers); ?>
function <?php echo $filter_func_name; ?>($data) {
    $filters = array(
<?php foreach ($filters as $filter): ?>
        '<?php echo $filter['name'] ?>' => array(<?php echo $this->_renderArgs($filter['args']) ?>),
<?php endforeach; ?>
    );
    
    foreach ($filters as $className => $args) {
        if (!is_subclass_of($className, '\ClassQL\Filter')) {
            throw new \ClassQL\Exception("Filter '$className' must subclass '\ClassQL\Filter'");
        }
        $data = new $className($data, $args);
    }
    
    return $data;
}
<?php endif; ?>