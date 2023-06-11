<?php

namespace controller\graphql\nodes\types\type;

use controller\graphql\nodes\types\BaseType;

class UserType extends BaseType {

    public int $id;
    public string $givenName;
    public string $familyName;
    // public string $email;
    // public string $password;
    // public string $tel;
    // public DateTime $bday;
    public AddressType $address;

    public function __construct() {
        $this->schemaName = 'user';
    }

}