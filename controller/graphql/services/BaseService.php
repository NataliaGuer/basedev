<?php

namespace controller\graphql\services;

use controller\graphql\operations\BaseOperation;
use controller\graphql\types\BaseType;
use PhpParser\Node\Scalar\String_;

class BaseService{
    
    public array $operationToMethod;

    public function handle(BaseOperation $operation): BaseType{
        $method = $this->operationToMethod[get_class($operation)];
        return $this->$method($operation);
    }
}