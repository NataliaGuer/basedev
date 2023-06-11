<?php

namespace controller\graphql\nodes;

use controller\graphql\nodes\operations\OperationNode;

class QueryNode {
    
    public string $name;

    public QueryType $type;
    
    /** @var ParameterNode[] */
    public array $parameters;

    /** @var OperationNode[] */
    public array $operations;

    public function getParameterFromAlias(string $alias): ?ParameterNode{
        $res = null;
        foreach($this->parameters as $parameter){
            if($parameter->alias === $alias){
                $res = $parameter;
                break;
            }
        }
        return $res;
    }

}