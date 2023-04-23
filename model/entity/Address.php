<?php

namespace model\entity;

class Address extends BaseEntity
{

	public $id;
	public $country;

	/**
	 * array che mappa i nomi degli attributi della classe nel nome delle colonne della 
	 * tabella che l'entità rappresenta	
	 */
	protected static $attributeToColumnMapper = [
		"id"      => "id",
		"country" => "country"
	];

	public function set(int $id, string $country)
	{
		$this->id = $id;
		$this->country = $country;
	}

	public function getKey()
	{
		return $this->id;
	}

}