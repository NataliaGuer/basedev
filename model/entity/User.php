<?php

namespace model\entity;

class User extends BaseEntity
{

	public $id;
	/** @var string nome dell'utente*/
	public $name;
	/** @var string cognome dell'utente*/
	public $surname;
	public $address;

	protected static $attributeToColumnMapper = [
		"id" => "id",
		"name" => "name",
		"surname" => "surname",
		"address" => "address"
	];

	public function set(int $id, string $name, string $surname, int $address)
	{
		$this->id = $id;
		$this->name = $name;
		$this->surname = $surname;
		$this->address = $address;
	}

	public function getKey()
	{
		return $this->id;
	}
}