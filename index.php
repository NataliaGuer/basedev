<?php

//use Composer\Autoload\ClassLoader;
// use model\database\Connection;
// use PHPUnit\Framework\TestCase;

use model\database\Connection;

require_once __DIR__ . '/vendor/autoload.php';
// $loader = new ClassLoader();

// $loader->register();
// $loader->setUseIncludePath(true);

$connection = Connection::getConnection();
var_dump($connection);