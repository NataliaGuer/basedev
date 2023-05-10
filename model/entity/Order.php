<?php

namespace model\entity;

class Order extends BaseEntity
{

	public $id;
	public $userId;

	public function set(int $id, int $userId)
	{
		$this->id = $id;
		$this->userId = $userId;
	}

	public function getKey()
	{
		return $this->id;
	}
}