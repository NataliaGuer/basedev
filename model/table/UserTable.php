<?php

namespace model\table;

use model\entity\User;
use model\entitySet\UserSet;
use model\database\query\joinType;
use model\database\query\statement;
use model\database\query\where;
use model\relationship\OrderUserRelationship;
use model\relationship\UserAddressRelationship;

/**
 * @method User|UserSet|bool execute()
 */
class UserTable extends BaseTable
{
	public static $tableName = 'user';
	public static $entityClass = User::class;
	public static $entitySetClass = UserSet::class;

	protected static $primaryKey = ["id"];

	public function joinAddress(joinType $joinType)
	{
		$this->join(UserAddressRelationship::get(), $joinType);
		return $this;
	}

	public function delete($id)
	{
		$this->statement = statement::DELETE;
		$this->where(self::$tableName, self::$primaryKey[0], where::EQUAL, $id);
		return $this->execute();
	}
}