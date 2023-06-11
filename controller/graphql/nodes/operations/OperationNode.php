<?php

namespace controller\graphql\nodes\operations;

use controller\graphql\nodes\ParameterNode;
use controller\graphql\nodes\types\BaseType;
use controller\graphql\nodes\types\union\BaseUnion;

class OperationNode
{
    public string $name;

    /** @var ParameterNode[] */
    public array $parameters;

    public BaseType|BaseUnion $result;

    public function getParameterByName(string $name): ?ParameterNode
    {
        $res = null;
        foreach ($this->parameters as $parameter) {
            if ($parameter->name === $name) {
                $res = $parameter;
                break;
            }
        }
        return $res;
    }

    public function getResult(): array
    {
        return $this->result->toArray();
    }
}
