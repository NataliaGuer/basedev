<?php

namespace controller\graphql\nodes\types\type;

use controller\graphql\nodes\types\BaseType;

class AddressType extends BaseType{

    public int $id;
    public string $country;
    
    public function __construct() {
        $this->schemaName = 'address';
    }

}