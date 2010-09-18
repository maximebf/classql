// ----------------------------------------------------------------------------
// Functions for '<?php echo $name ?>'

<?php if (!empty($docComment)): ?>
/**<?php echo $docComment ?>
*/
<?php endif; ?>
<?php if (!empty($modifiers)) echo implode(' ', $modifiers) . ' '; ?>
function <?php echo $name; ?>(<?php echo implode(', ', $params); ?>) {
<?php if (isset($query)): ?>
    $stmt = <?php echo $scope . $execute_func_name; ?>(<?php echo implode(', ', array_keys($params)); ?>);
<?php if (!empty($filters)): ?>
    return <?php echo $filter_func_name; ?>($stmt->fetchAll(PDO::FETCH_ASSOC));
<?php else: ?>
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
<?php endif; ?>
<?php else: ?>
    return <?php echo $callback['name'] ?>();
<?php endif; ?>
}

<?php if (isset($query)): ?>
/**
 * Creates and executes the statement associated to {@see <?php echo $name ?>()} 
 * @return PDOStatement
 */
<?php if (!empty($modifiers)) echo implode(' ', $modifiers) . ' '; ?>
function <?php echo $execute_func_name; ?>(<?php echo implode(', ', $params); ?>) {
    $stmt = <?php echo $scope . $statement_func_name ?>();
    $params = array(<?php echo implode(', ', array_keys($params)); ?>);
    
    if ($stmt->execute($params) === false) {
        throw new \ClassQL\DatabaseException($stmt);
    }
    
    return $stmt;
}

/**
 * Creates the statement associated to {@see <?php echo $name ?>()} 
 * @return PDOStatement
 */
<?php if (!empty($modifiers)) echo implode(' ', $modifiers) . ' '; ?>
function <?php echo $statement_func_name; ?>() {
    $query = '<?php echo str_replace('\'', '\\\'', $this->_parameterizeQuery($query['sql'])); ?>';
    return \ClassQL\Session::getConnection()->prepare($query);
}

<?php endif; ?>
<?php if (!empty($filters)): ?>
/**
 * Filters the fetched results for {@see <?php echo $name ?>()} 
 * @return array
 */
<?php if (!empty($modifiers)) echo implode(' ', $modifiers) . ' '; ?>
function <?php echo $filter_func_name; ?>($results) {
    $filters = array(
<?php foreach ($filters as $filter): ?>
        new \<?php echo $filter['name'] ?>(<?php echo implode(', ', $filter['args']) ?>),
<?php endforeach; ?>
    );
    
    foreach ($filters as $filter) {
        $results = call_user_func(array($filter, 'filter'), $results);
    }
    
    return $results;
}
<?php endif; ?>