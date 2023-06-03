<?php

namespace controller\graphql\operations;

use controller\graphql\types\UserType;

class GetUserOperation extends BaseOperation{

    public function __construct()
    {
        $this->name = 'getUser';
        $this->type = OperationType::QUERY; 
        $this->returnType = UserType::class; 

        $this->requiredParams = [
            "id" => "ID!"
        ];
    }
}