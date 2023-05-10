<?php

namespace controller\graphql\types;

use model\entity\Address;

class AddressType extends BaseType{

    public int $id;
    public string $country;

    public function constructFromModel(Address $address)
    {
        $this->id = $address->id;
        $this->country = $address->country;
    }
}