// ----------------------------------------------------------------------------
// Functions for '<?php echo $name ?>'

<?php if (!empty($docComment)): ?>
    /**<?php echo $docComment ?>
    */
<?php endif; ?>
<?php echo $this->_renderModifiers($modifiers); ?> function <?php echo $name; ?>(<?php echo implode(', ', $params); ?>) {
    $conn = \ClassQL\Session::getConnection(<?php echo $this->_getConnectionName() ?>);

    <?php if ($this->_hasAttribute($attributes, 'IdentityMap')): ?>
        if ($data = \ClassQL\Cache\IdentityMap::get(__CLASS__, array(<?php echo implode(', ', array_keys($params)); ?>))) {
            return $data;
        }
    <?php endif; ?>
    <?php if ($this->_hasAttribute($attributes, 'CachedProperty')): ?>
        if (<?php echo $this->_renderVar("\${$name}Cache", true) ?> !== null) {
            return <?php echo $this->_renderVar("\${$name}Cache", true) ?>;
        }
    <?php endif; ?>

    <?php if ($this->_hasAttribute($attributes, 'Cached')): ?>
        $__cacheId = $conn->cacheId(<?php echo $this->_renderCacheIdArgs($attributes) ?>);
        if (!($data = $conn->getCache()->get($__cacheId))) {
    <?php endif; ?>

    <?php if (isset($query)): ?>
        $stmt = <?php echo $this->_renderScope($modifiers) . $execute_func_name; ?>(<?php echo implode(', ', array_keys($params)); ?>);

        <?php if ($this->_hasAttribute($attributes, 'IdentityOnly')): ?>
            <?php list($identityResolverCallback, $identityResolver) = $this->_getIdentityResolver() ?>
            <?php if ($query['returns']['type'] == 'collection'): ?>
                $data = array();
                if (($ids = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0)) !== false) {
                    <?php if ($this->_hasAttribute($identityResolver['attributes'], 'CacheMulti')): ?>
                        $data = <?php echo $this->_renderScope($modifiers) . $this->_getAttribute($identityResolver['attributes'], 'CacheMulti'); ?>($ids);
                    <?php else: ?>
                        $data = array_map('<?php echo $identityResolverCallback ?>', $ids);
                    <?php endif; ?>
                    $data = array_filter($data);
                }
            <?php else: ?>
                $data = false;
                if (($id = $stmt->fetch(\PDO::FETCH_COLUMN, 0)) !== false) {
                    $data = <?php echo $identityResolverCallback ?>($id);
                    $stmt->closeCursor();
                }
            <?php endif; ?>

        <?php elseif ($query['returns']['type'] != 'null'): ?>

            <?php if (in_array($query['returns']['type'], array('collection', 'class', 'update'))): ?>
                <?php foreach ($this->_getMappedColumnTypes() as $column => $type): ?>
                    $stmt->setColumnType('<?php echo $column ?>', '<?php echo $type ?>');
                <?php endforeach; ?>
            <?php endif; ?>
            <?php if (isset($query['returns']['with'])): ?>
                $data = $stmt->fetch<?php if ($query['returns']['type'] == 'collection') echo 'All' ?>Composite('<?php 
                            echo $query['returns']['value'] ?>', <?php echo $this->_renderMappingInfo($query['returns']) ?>, <?php echo $this->_hasMappedColumns() ? 'true' : 'false' ?>);
            <?php elseif ($query['returns']['type'] == 'collection'): ?>
                $data = $stmt->fetchAll(\PDO::FETCH_CLASS<?php echo $this->_hasMappedColumns() ? ' | \ClassQL\Database\Connection::FETCH_TYPED' : '' ?>, '<?php echo $query['returns']['value'] ?>') ?: array();
            <?php elseif ($query['returns']['type'] == 'class'): ?>
                $stmt->setFetchMode(\PDO::FETCH_CLASS<?php echo $this->_hasMappedColumns() ? ' | \ClassQL\Database\Connection::FETCH_TYPED' : '' ?>, '<?php echo $query['returns']['value'] ?>');
                $data = $stmt->fetch();
                $stmt->closeCursor();
            <?php elseif ($query['returns']['type'] == 'value'): ?>
                $data = $stmt->fetchColumn();
                $stmt->closeCursor();
            <?php elseif ($query['returns']['type'] == 'value_collection'): ?>
                $data = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0) ?: array();
            <?php elseif ($query['returns']['type'] == 'last_insert_id'): ?>
                $this->id = $conn->lastInsertId();
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
            <?php $cacheArgs = $this->_renderArgs($this->_getAttributeArgs($attributes, 'Cached'), array_keys($params)); ?>
            $conn->getCache()->add($__cacheId, $data<?php if (!empty($cacheArgs)) echo ", $cacheArgs"; ?>);
        }
    <?php endif; ?>

    <?php if ($this->_hasAttribute($attributes, 'InvalidateIdentity')): ?>
        <?php list($identityResolverCallback, $identityResolver) = $this->_getIdentityResolver() ?>
        $conn->invalidateCache($conn->cacheId(<?php echo $this->_renderCacheIdArgs($identityResolver['attributes']) ?>));
    <?php endif; ?>

    <?php foreach ($this->_getAttributes($attributes, 'InvalidateCache') as $attr): ?>
        $conn->invalidateCache($conn->cacheId(<?php echo $this->_renderCacheIdArgsFromArgs($attr['args']) ?>));
    <?php endforeach; ?>

    <?php if (!isset($query) || in_array($query['returns']['type'], array('value', 'value_collection', 'class', 'collection'))):?>
        <?php if ($this->_hasAttribute($attributes, 'IdentityMap')): ?>
            \ClassQL\Cache\IdentityMap::set(__CLASS__, array(<?php echo implode(', ', array_keys($params)); ?>), $data);
        <?php endif; ?>
        <?php if ($this->_hasAttribute($attributes, 'CachedProperty')): ?>
            <?php echo $this->_renderVar("\${$name}Cache", true) ?> = $data;
        <?php endif; ?>
        return $data;
    <?php endif; ?>
}

<?php if ($this->_hasAttribute($attributes, 'CacheMulti')): ?>

    <?php echo $this->_renderModifiers($modifiers); ?>
    function <?php echo $this->_getAttributeValue($attributes, 'CacheMulti') ?>($args) {
        $conn = \ClassQL\Session::getConnection(<?php echo $this->_getConnectionName() ?>);
        $__cacheIds = array();
        foreach ($args as $arg) {
            $params = array_combine(array('<?php echo implode("', '", array_map(function($v) { return substr($v, 1); }, array_keys($params))) ?>'), $arg);
            extract($params);
            $__cacheIds[] = $conn->cacheId(<?php echo $this->_renderCacheIdArgs($attributes) ?>);
        }
        $cachedObjects = $conn->getCache()->getMulti($__cacheIds);
    }

<?php endif; ?>

<?php if (isset($query)): ?>

    /**
     * Creates and executes the statement associated to {@see <?php echo $name ?>()} 
     * @return PDOStatement
     */
    <?php echo $this->_renderModifiers($modifiers); ?>
    function <?php echo $execute_func_name; ?>(<?php echo implode(', ', $params); ?>) {
        $sqlString = <?php echo $this->_renderScope($modifiers) . $query_func_name; ?>(<?php echo implode(', ', array_keys($params)); ?>);
        $stmt = \ClassQL\Session::getConnection(<?php echo $this->_getConnectionName() ?>)->prepare($sqlString->sql);
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