<?php

namespace controller\graphql\nodes\types;

use controller\graphql\nodes\FieldNode;

class BaseType{

    public string $schemaName;

    public string $toReturnAs = "";

    /** @var FieldNode[] campi selezionati nella query (quelli che vengono restituiti) */
    public array $fieldsToReturn;

    /**
     * metodo che restituisce la rappresentazione sotto forma di array dell'oggetto  
     * se $fields è valorizzato, nella rappresentazione saranno contenute solo le proprietà il cui nome compare in esso
     * @return array
     */
    public function toArray(): array{
        $res = [];
        if ($this->fieldsToReturn) {
            foreach ($this->fieldsToReturn as $field) {
                $fieldName = $field->toReturnAs ?: $field->name;
                if(property_exists($this, $fieldName)){
                    $fieldValue = $this->$fieldName;
                    if($fieldValue instanceof BaseType){
                        $res[$fieldName] = $fieldValue->toArray([$field]);
                    } else {
                        $res[$fieldName] = $fieldValue;
                    }
                }
            }
        } else {
            foreach ($this as $key => $value) {
                if($value instanceof BaseType){
                    $res[$key] = $value->toArray();
                } else {
                    $res[$key] = $value;
                }
            }
        }
        return $res;
    }
}