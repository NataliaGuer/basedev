<?php

namespace model\table;

use model\entity\Order;
use model\entity\OrderSet;
use model\database\query\joinType;
use model\relationship\OrderUserRelationship;

class OrderTable extends BaseTable
{
	public static $tableName = 'order';
	public static $entityClass = Order::class;
	public static $entitySetClass = OrderSet::class;
	
	public function joinUser(joinType $joinType)
	{
		$this->join(OrderUserRelationship::get(), $joinType);
		return $this;
	}
}