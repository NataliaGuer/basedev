<?php

namespace controller\graphql\nodes\types\union;

use controller\graphql\nodes\FieldNode;
use controller\graphql\nodes\types\BaseType;
use Exception;

class BaseUnion extends BaseType{

    /** @var BaseType[] */
    public array $possibleTypes;

    protected BaseType $finalType;

    /**
     * @param FieldNode[] $fields
     * @param string $typeClass
     * 
     * @return void
     */
    public function setFieldsToReturn(array $fields, string $typeName){
        foreach ($this->possibleTypes as &$type) {
            if ($type->schemaName === $typeName) {
                $type->fieldsToReturn = $fields;
                break;
            }
        }
    }

    public function castTo($typeName){
        foreach ($this->possibleTypes as &$type) {
            if ($type->schemaName === $typeName) {
                $this->finalType = $type;
                break;
            }
        }
    }

    public function toArray(): array{
        $res = [];
        if($this->finalType){
            $res = $this->finalType->toArray();
        }
        return $res;
    }
}