<?php

namespace model\relationship;

use model\table\UserTable;
use model\table\AddressTable;

class UserAddressRelationship extends BaseRelationship
{
	private static $instance;

	private function __construct()
	{
		$this->parentTable = AddressTable::$tableName;
		$this->parentTableColumn = "id";
		$this->childTable = UserTable::$tableName;
		$this->childTableColumn = "address";
	}

	public static function get()
	{
		if (self::$instance === null) {
			self::$instance = new UserAddressRelationship();
		}
		return self::$instance;
	}
}