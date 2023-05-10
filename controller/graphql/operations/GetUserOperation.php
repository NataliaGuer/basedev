<?php

namespace controller\graphql\operations;

use controller\graphql\types\UserType;

class GetUserOperation extends BaseOperation{

    // in input
    /*
    getUser(id: $id){
        n
    }
    variables: {
       id: "1",
    }
    */
    public function __construct($query, $variables)
    {
        $this->rawQuery = $query;
        $this->type = OperationType::QUERY;
        $this->name = 'getUser';
        $this->variables = [
            "id" => $variables[$this->getQueryVariableNameFor("id")]
        ];
        $this->returnType = UserType::class;
        //aggiunta metodo per settare a priori nel type il sottoinsieme di variabili desiderate
        $this->setVariblesToReturn();
    }
}