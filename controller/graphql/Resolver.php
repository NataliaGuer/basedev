<?php
namespace controller\graphql;

use controller\graphql\nodes\operations\OperationNode;
use controller\graphql\parse\Parser;
use controller\graphql\services\BaseService;

class Resolver {

    protected $parser;
    protected $schemaManager;

    public function __construct(){
        $this->parser = new Parser;
        $this->schemaManager = SchemaManager::getInstance();
    }
    
    /**
     * metodo che si occupa di elaborare la richiesta estrapolando la query e le variabili,
     * mappa ogni operazione contenuta nella query nel metodo di una classe di tipo BaseOperation e 
     * colleziona i risultati nell'oggetto Response
     * 
     * @param string $request la richiesta completa di operationName, query e variables
     * @return Response
     */
    public function resolve(string $request): Response {
        $request = json_decode($request, true);
        $response = new Response();
        try {
            $queryNode = $this->parser->parseQuery($request["query"], $request["variables"]);
            foreach ($queryNode->operations as $operation) {
                $this->resolveOperation($operation);
                $response->registerOperation($operation);
            }
        } catch (\Throwable $th) {
            //log
            $error = $th instanceof Error ? $th : (new Error(null, $th));
            $response->registerError($error);
        }
        return $response;
    }

    protected function checkRequestValidity($query){

    }

    protected function resolveOperation(OperationNode &$operation){
        /** @var BaseService */
        $service = null;
        $operationName = $operation->name;
        [$serviceClass, $method] = $this->schemaManager->getOperationResolver($operation);
        try {
            $service = new $serviceClass();
            $service->$method($operation);
        } catch (\Throwable $th) {
            $error = new Error(Error::GRAPHQL_NO_SERVICE_FOR_OPERATION);
            $error->setExtraData([
                "operationName" => $operationName
            ]);
            throw $error;
        }
    }
}