<?php

use model\database\build\EntityBuilder;
use model\database\build\EntitySetBuilder;
use model\database\build\RelationshipBuilder;
use model\database\build\TableBuilder;

require_once '/var/www/rdb/vendor/autoload.php';

echo "Inserisci il nome della tabella di cui vuoi generare l'Entity [no per uscire]: ";
$handle = fopen ("php://stdin","r");
$line = fgets($handle);
$tableName = trim($line);
if($tableName == 'no'){
    echo "ABORTING!\n";
    exit;
}

$eb = new EntityBuilder();
$esb = new EntitySetBuilder();
$rb = new RelationshipBuilder();
$tb = new TableBuilder();
try {
    echo "\nGenerazione Entity...\n";
    $eb->build($tableName);
    echo "Entity generata\n";

    echo "\nGenerazione EntitySet...\n";
    $esb->build($tableName);
    echo "EntitySet generato\n";

    echo "\nGenerazione relationships...\n";
    $rb->build($tableName);
    echo "relationships generate\n";

    
    echo "\nGenerazione table...\n";
    $tb->build($tableName);
    echo "table generata\n";
} catch (\Throwable $th) {
    $mess = $th->getMessage();
    echo "Errore: $mess\n";
}
fclose($handle);