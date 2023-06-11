<?php

namespace controller\graphql;

use controller\graphql\nodes\operations\OperationNode;

class Response {
    public array $data;
    public $errors;

    public function registerOperation(OperationNode $operation){
        //inserimento in data dell'associazione nome dell'operazione/campi valorizzati del type
        //esempio: operazione getUser:
        $this->data[$operation->name] = $operation->getResult();
    }

    public function registerError(Error $error){
        
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