<?php

require_once __DIR__ . '/../libs/ClassQL/Loader.php';
ClassQL\Loader::register('Parsec', __DIR__ . '/../vendor/parsec/libs/Parsec');
ClassQL\Loader::register('ClassQL', __DIR__ . '/../libs/ClassQL');
ClassQL\Loader::register('Demo\Models', __DIR__ . '/libs/Models', true);
ClassQL\Loader::register('Demo', __DIR__ . '/libs');

ClassQL\Session::start(array(
    'dsn' => 'sqlite:./demo.db',
    'streamcache' => __DIR__ . '/cache',
    'profiler' => new ClassQL\Database\FileProfiler('queries.log')
));
