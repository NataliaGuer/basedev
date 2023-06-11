<?php

namespace controller\graphql\nodes;

class FieldNode {
    public string $name;
    
    /** @var FieldNode[] */
    public array $fields;
    
    /** @var FieldArgumentNode[] */
    public array $arguments;

    /**
     * contiene l'alias scelto per il campo al momento della scrittura della query
     * ovvero il nome con cui deve essere restituito il valore ottuto dalla query per il campo
     * @var string
     */
    public string $toReturnAs = "";
}