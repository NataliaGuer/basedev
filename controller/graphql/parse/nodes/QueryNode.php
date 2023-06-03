<?php

namespace controller\graphql\parse\nodes;

use controller\graphql\schemaTypes\query as queryType;

class QueryNode {
    
    public string $name;

    public queryType $type;
    
    /** @var ParameterNode[] */
    public array $parameters;

    /** @var OperationNode[] */
    public array $operations;

    public function getParameterFromAlias(string $alias){
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