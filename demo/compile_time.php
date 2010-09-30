<?php 

require_once __DIR__ . '/bootstrap.php';

$cacheStatus = ClassQL\Session::getCache() !== null ? "cache enabled" : "without cache";
echo "Including models with $cacheStatus\n";
$start = microtime(true);

new Demo\User();
new Demo\Message();

$time = microtime(true) - $start;
echo "Execution time: $time\n";
