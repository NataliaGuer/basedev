<?php

namespace controller\graphql;

use controller\graphql\operations\BaseOperation;
use controller\graphql\operations\OperationType;

class Request{
    // {
    //     operationName: "getUser",
    //     query: "
    //       query getUser($id: ID!){
    //         getUser(id: $id){
    //           n
    //         }
    //       }
    //     ",
    //     variables: {
    //       id: "1",
    //     }
    // }

    public $operationName;
    public OperationType $operationType;
    public array $operations;
    public $variables;

    public function __construct(array $data){
        $this->operationName = $data['operationName'];
        $this->operationType = $this->getOperationType($data["query"]);
        $this->operations = $this->getOperations($data["query"]);
        $this->variables = $data['variables'];
    }

    protected function getOperationType(string $query): OperationType {
        
    }
}