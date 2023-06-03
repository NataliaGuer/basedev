<?php

namespace controller\graphql;

use controller\graphql\operations\BaseOperation;
use controller\graphql\types\BaseType;

class Response {
    public array $data;
    public $errors;

    public function registerOperationResult(BaseOperation $operation, BaseType $result){
        //inserimento in data dell'associazione nome dell'operazione/campi valorizzati del type
        //esempio: operazione getUser:
        $this->data[$operation->name] = $result->toArray($operation->getFieldsToReturn());
    }

    public function toArray(): array {
        $res = [
            "data" => $this->data
        ];
        if($this->errors){
            $res["errors"] = $this->errors;
        }
        return $res;
    }
}