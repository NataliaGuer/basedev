<?php

namespace controller\graphql\parse\nodes;

class OperationNode {
    public string $name;
    /** @var ParameterNode[] */
    public array $parameters;
    /** @var FieldNode[] */
    public array $fieldsToReturn;
}