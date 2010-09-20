// ----------------------------------------------------------------------------
// Functions for '<?php echo $name ?>'

<?php if (!empty($docComment)): ?>
/**<?php echo $docComment ?>
*/
<?php endif; ?>
<?php echo $this->_renderModifiers($modifiers, true); ?>
function <?php echo $name; ?>(<?php echo implode(', ', $params); ?>) {
<?php if (isset($query)): ?>
    $stmt = <?php echo $scope . $execute_func_name; ?>(<?php echo implode(', ', array_keys($params)); ?>);
<?php if ($query['returns']['type'] != 'null'): ?>
<?php if ($query['returns']['type'] == 'collection'): ?>
    $data = new \ClassQL\Collection($stmt, '<?php echo $query['returns']['value'] ?>');
<?php elseif ($query['returns']['type'] == 'class'): ?>
    $data = new <?php echo $query['returns']['value'] ?>($stmt->fetch(PDO::FETCH_ASSOC));
<?php elseif ($query['returns']['type'] == 'value'): ?>
    $data = $stmt->fetchColumn();
<?php endif; ?>
<?php endif; ?>
<?php else: ?>
    $data = <?php echo $callback['name'] ?>(<?php echo $this->_renderArgs($callback['args'], array_keys($params)) ?>);
<?php endif; ?>
<?php if (!isset($query) || $query['returns']['type'] != 'null'):?>
<?php if (!empty($filters)): ?>
    return <?php echo $scope . $filter_func_name; ?>($data);
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
<?php echo $this->_renderModifiers($modifiers, true); ?>
function <?php echo $execute_func_name; ?>(<?php echo implode(', ', $params); ?>) {
    $stmt = <?php echo $scope . $statement_func_name ?>();
    $params = array(<?php echo $this->_renderQueryParams($query['vars'], array_keys($params)) ?>);
    
    if ($stmt->execute($params) === false) {
        throw new \ClassQL\DatabaseException($stmt);
    }
    
    return $stmt;
}

/**
 * Creates the statement associated to {@see <?php echo $name ?>()} 
 * @return PDOStatement
 */
<?php echo $this->_renderModifiers($modifiers, true); ?>
function <?php echo $statement_func_name; ?>() {
<?php if (empty($scope)): ?>
    $query = '<?php echo $this->_renderQuery($query, $query['vars']); ?>';
<?php else: ?>
    $query = <?php echo $this->_renderQueryInClass($query, array_keys($params)) ?>;
<?php endif; ?>
    return \ClassQL\Session::getPDO()->prepare($query);
}
<?php endif; ?>
<?php if (!empty($filters)): ?>

/**
 * Filters the fetched data for {@see <?php echo $name ?>()} 
 * @return array
 */
<?php echo $this->_renderModifiers($modifiers, true); ?>
function <?php echo $filter_func_name; ?>($data) {
    $filters = array(
<?php foreach ($filters as $filter): ?>
        '<?php echo $filter['name'] ?>' => array(<?php echo $this->_renderArgs($filter['args']) ?>),
<?php endforeach; ?>
    );
    
    foreach ($filters as $className => $args) {
        if (!is_subclass_of('$className', '\ClassQL\Filter')) {
            throw new \ClassQL\Exception("Filter '$className' must subclass '\ClassQL\Filter'");
        }
        $data = new $className($data, $args);
    }
    
    return $data;
}
<?php endif; ?>