<?php

use model\database\query\where;
use model\entity\Address;
use model\table\AddressTable;
use model\table\UserTable;

require_once __DIR__ . '/vendor/autoload.php';
$addressTable = new AddressTable();
$addressEntity = $addressTable->select()
                    ->where(AddressTable::$tableName, Address::getColumnName("id"), where::EQUAL, 1)
                    ->execute();
$addressEntity->country = "italy";
$addressTable->update($addressEntity);

$actual = $addressTable->select()
            ->where(AddressTable::$tableName, Address::getColumnName("id"), where::EQUAL, 1)
            ->execute();

print_r($actual);

$actual = $addressTable->select()
            ->where(AddressTable::$tableName, Address::getColumnName("id"), where::EQUAL, 1)
            ->execute();

print_r($actual);