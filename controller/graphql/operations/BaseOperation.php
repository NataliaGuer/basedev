<?php

namespace controller\graphql\operations;

abstract class BaseOperation {
    
    public OperationType $type;
    public string $name;
    public array $variables;
    public string $returnType;
    public array $fieldToReturn;
    public string $rawQuery;

}