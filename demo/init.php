<?php 

require_once __DIR__ . '/bootstrap.php';

use ClassQL\Session,
    ClassQL\Parser\Parser,
    ClassQL\Generator\SQLGenerator;

$parser = new Parser();
$descriptor = $parser->parseFile($_SERVER['argv'][1]);
$generator = new SQLGenerator();
$sql = $generator->generate($descriptor);

Session::getConnection()->exec($sql);