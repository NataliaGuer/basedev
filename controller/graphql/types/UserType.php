<?php

namespace controller\graphql\types;

use model\entity\Address;
use model\entity\User;

class UserType extends BaseType {
    public int $id;
    public string $givenName;
    public string $familyName;
    // public string $email;
    // public string $password;
    // public string $tel;
    // public DateTime $bday;
    public AddressType $address;

    public function constructFromModel(User $user, Address $address){
        $this->id = $user->id;
        $this->givenName = $user->name;
        $this->familyName = $user->surname;
        $addressType = new AddressType();
        $this->address = $addressType->constructFromModel($address);
    }
}