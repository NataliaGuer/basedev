<?php

namespace model\relationship;

use model\table\OrderTable;
use model\table\UserTable;

class OrderUserRelationship extends BaseRelationship
{
	private static $instance;

	private function __construct()
	{
		$this->parentTable = UserTable::$tableName;
		$this->parentTableColumn = "id";
		$this->childTable = OrderTable::$tableName;
		$this->childTableColumn = "user_id";
	}

	public static function get()
	{
		if (self::$instance === null) {
			self::$instance = new OrderUserRelationship();
		}
		return self::$instance;
	}
}