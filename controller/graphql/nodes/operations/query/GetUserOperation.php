<?php

namespace controller\graphql\nodes\operations\query;

use controller\graphql\nodes\operations\OperationNode;
use controller\graphql\nodes\types\type\UserType;

class GetUserOperation extends OperationNode{

    public function __construct()
    {
        $this->name = 'getUser';
        $this->result = new UserType();
    }
}