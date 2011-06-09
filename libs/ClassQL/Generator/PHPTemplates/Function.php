// ----------------------------------------------------------------------------
// Functions for '<?php echo $name ?>'

<?php if (!empty($docComment)): ?>
    /**<?php echo $docComment ?>
    */
<?php endif; ?>
<?php echo $this->_renderModifiers($modifiers); ?> function <?php echo $name; ?>(<?php echo implode(', ', $params); ?>) {

    <?php if ($this->_hasAttribute($attributes, 'CachedProperty')): ?>
        if (<?php echo $this->_renderVar("\${$name}Cache", true) ?> === null) {
    <?php endif; ?>

    <?php if ($this->_hasAttribute($attributes, 'Cached')): ?>
        $__cacheId = \ClassQL\Session::getConnection()->cacheId(<?php echo $this->_renderCacheIdArgs($attributes) ?>);
        if (\ClassQL\Session::getConnection()->getCache()->has($__cacheId)) {
            <?php if ($this->_hasAttribute($attributes, 'CachedProperty')): ?>
                    <?php echo $this->_renderVar("\${$name}Cache", true) ?> = \ClassQL\Session::getCache()->get($__cacheId);
                    return <?php echo $this->_renderVar("\${$name}Cache", true) ?>;
            <?php else: ?>
                    return \ClassQL\Session::getConnection()->getCache()->get($__cacheId);
            <?php endif; ?>
        }
    <?php endif; ?>

    <?php if (isset($query)): ?>
        $stmt = <?php echo $this->_renderScope($modifiers) . $execute_func_name; ?>(<?php echo implode(', ', array_keys($params)); ?>);

        <?php if ($this->_hasAttribute($attributes, 'IdentityOnly')): ?>
            <?php list($identityResolverCallback, $identityResolver) = $this->_getIdentityResolver() ?>
            $ids = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);
            $data = array_map('<?php echo $identityResolverCallback ?>', $ids);

        <?php elseif ($query['returns']['type'] != 'null'): ?>

            <?php foreach ($this->_getMappedColumnTypes() as $column => $type): ?>
                $stmt->setColumnType('<?php echo $column ?>', '<?php echo $type ?>');
            <?php endforeach; ?>
            <?php if (isset($query['returns']['with'])): ?>
                $data = $stmt->fetch<?php if ($query['returns']['type'] == 'collection') echo 'All' ?>Composite('<?php 
                            echo $query['returns']['value'] ?>', <?php echo $this->_renderMappingInfo($query['returns']) ?>, <?php echo $this->_hasMappedColumns() ? 'true' : 'false' ?>);
            <?php elseif ($query['returns']['type'] == 'collection'): ?>
                $data = $stmt->fetchAll(\PDO::FETCH_CLASS<?php echo $this->_hasMappedColumns() ? ' | \ClassQL\Database\Connection::FETCH_TYPED' : '' ?>, '<?php echo $query['returns']['value'] ?>');
            <?php elseif ($query['returns']['type'] == 'class'): ?>
                $stmt->setFetchMode(\PDO::FETCH_CLASS<?php echo $this->_hasMappedColumns() ? ' | \ClassQL\Database\Connection::FETCH_TYPED' : '' ?>, '<?php echo $query['returns']['value'] ?>');
                $data = $stmt->fetch();
                $stmt->closeCursor();
            <?php elseif ($query['returns']['type'] == 'value'): ?>
                $data = $stmt->fetchColumn();
                $stmt->closeCursor();
            <?php elseif ($query['returns']['type'] == 'value_collection'): ?>
                $data = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);
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

    <?php if ($this->_hasAttribute($attributes, 'Cached')): ?>
        \ClassQL\Session::getConnection()->getCache()->set($__cacheId, $data);
    <?php endif; ?>

    <?php if ($this->_hasAttribute($attributes, 'InvalidateIdentity')): ?>
        <?php list($identityResolverCallback, $identityResolver) = $this->_getIdentityResolver() ?>
        \ClassQL\Session::getConnection()->invalidateCache(
            \ClassQL\Session::getConnection()->cacheId(<?php echo $this->_renderCacheIdArgs($identityResolver['attributes']) ?>));
    <?php endif; ?>

    <?php foreach ($this->_getAttributes($attributes, 'InvalidateCache') as $attr): ?>
        \ClassQL\Session::getConnection()->invalidateCache(
            \ClassQL\Session::getConnection()->cacheId(<?php echo $this->_renderCacheIdArgsFromArgs($attr['args']) ?>));
    <?php endforeach; ?>

    <?php if (!isset($query) || in_array($query['returns']['type'], array('value', 'value_collection', 'class', 'collection'))):?>
        <?php if ($this->_hasAttribute($attributes, 'CachedProperty')): ?>
            <?php echo $this->_renderVar("\${$name}Cache", true) ?> = $data;
            }
            return <?php echo $this->_renderVar("\${$name}Cache", true) ?>;
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
        $sqlString = <?php echo $this->_renderScope($modifiers) . $query_func_name; ?>(<?php echo implode(', ', array_keys($params)); ?>);
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
                <?php echo $inline['variable'] ?> =  new \ClassQL\SqlString(<?php echo $this->_renderInlineFunc($inline, array_keys($params)) ?>);
            <?php elseif ($inline['type'] == 'expression'): ?>
                <?php echo $inline['variable'] . ' = ' . $inline['expression'] ?>;
            <?php endif; ?>
        <?php endforeach; ?>
        $sql = "<?php echo $this->_renderQuery($query, array_keys($params)); ?>";
        $params = <?php echo $this->_renderQueryParams($query, array_keys($params)) ?>;
        return new \ClassQL\SqlString($sql, $params);
    }

<?php endif; ?>