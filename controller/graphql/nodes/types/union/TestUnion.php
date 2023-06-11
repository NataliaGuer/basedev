<?php

namespace controller\graphql\nodes\types\union;

use controller\graphql\nodes\types\type\AddressType;
use controller\graphql\nodes\types\type\UserType;

class TestUnion extends BaseUnion{

    public UserType $user;
    public AddressType $address;

    public function __construct() {
        $this->user = new UserType;
        $this->address = new AddressType;
        $this->possibleTypes = [
            $this->user,
            $this->address
        ];
    }


}