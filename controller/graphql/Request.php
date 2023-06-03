<?php

namespace controller\graphql;

use controller\graphql\operations\BaseOperation;
use controller\graphql\operations\OperationType;

class Request
{

    public string $operationName;
    public OperationType $operationType;
    /** @var BaseOperation[] */
    public array $operations;
    public array $variables;

    protected $parser;

    public function __construct(array $data)
    {
        $this->parser = new Parser();
        $parsedQuery  = $this->parser->parseQuery($data["query"]);
        //alias => valore
        $this->variables     = $data['variables'];
        $this->operationName = $data['operationName'];
        $this->operationType = $this->getOperationType($parsedQuery["type"]);
        $this->operations    = $this->getOperations($parsedQuery["operations"]);;
    }

    /**
     * converte una stringa in un case di OperationType  
     * nel caso in cui la stringa non sia uno dei valori dei case di OperationType viene scatenato un errore
     * @throws Error
     */
    protected function getOperationType(string $type): OperationType
    {
        return OperationType::from(strtoupper($type));
    }
    
    protected function getOperations(array $operations): array{
        $res = [];

        foreach ($operations as $operationName => $operation) {
            $op = $this->parser->parseOperation($operationName, $operation);
            $op->setVariables($this->variables);
            $res[$operationName] = $op;
        }

        return $res;
    }
}
