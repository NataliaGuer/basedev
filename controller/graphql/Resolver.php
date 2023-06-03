<?php
namespace controller\graphql;

use controller\graphql\operations\BaseOperation;
use controller\graphql\operations\GetUserOperation;
use controller\graphql\services\UserService;
use controller\graphql\services\BaseService;
use controller\graphql\types\BaseType;
use Exception;

class Resolver {

    protected $serviceOperationsAssoc = [
        UserService::class => [
            GetUserOperation::class
        ]
    ];

    public function resolve(string $query): Response {
        $response = new Response();
        try {
            $request = $this->createRequest($query);
            foreach ($request->operations as $operation) {
                $operationResult = $this->resolveOperation($operation);
                $response->registerOperationResult($operation, $operationResult);
            }
        } catch (\Throwable $th) {
            //log
            //inserire nella response un'istanza di Error contente tutte le informazioni
            throw new Exception("Error during request execution", 0, $th);
        }
        return $response;
    }

    protected function createRequest(string $query): Request{
        $query = json_decode($query, true);
        $req = new Request($query);
        return $req;
    }

    protected function checkRequestValidity($query){

    }

    protected function resolveOperation(BaseOperation $operation): BaseType{
        /** @var BaseService */
        $service = null;
        $operationClass = get_class($operation);
        foreach ($this->serviceOperationsAssoc as $serviceClass => $operations) {
            if(in_array($operationClass, $operations)){
                $service = new $serviceClass();
                break;
            }
        }
        if($service){
            //ogni service deve avere un array in cui mappa una certa operazione a un certo suo metodo pubblico
            return $service->handle($operation);
        } else {
            throw new \Exception("Operation $operationClass isn't mapped to any service, cannot resolve");
        }
    }
}