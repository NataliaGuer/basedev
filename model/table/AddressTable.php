<?php

namespace model\table;

use model\entity\Address;
use model\entitySet\AddressSet;

/**
 * @method Address|AddressSet|bool execute()
 */
class AddressTable extends BaseTable
{
    public static $tableName = 'address';
    public static $tableAlias = 'a';
    public static $entityClass = Address::class;
    public static $entitySetClass = AddressSet::class;

    protected static $primaryKey = ["id"];
}
