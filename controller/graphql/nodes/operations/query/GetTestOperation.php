<?php

namespace controller\graphql\nodes\operations\query;

use controller\graphql\nodes\types\union\TestUnion;
use controller\graphql\nodes\operations\OperationNode;

class GetTestOperation extends OperationNode{
    
    public function __construct() {
        $this->name = "getTest";
        $this->result = new TestUnion;
    }
}