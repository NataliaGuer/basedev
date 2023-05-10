<?php

namespace controller\graphql;

use controller\graphql\operations\BaseOperation;
use controller\graphql\types\BaseType;

class Response {
    public $data;

    public function registerOperationResult(BaseOperation $operation, BaseType $result){
        //inserimento in data dell'associazione nome dell'operazione/campi valorizzati del type
        //esempio: operazione getUser:
        //$this->data['getUser'] = $result;
    }

    public function toArray(): array {
        return [];
    }
}