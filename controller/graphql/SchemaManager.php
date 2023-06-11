<?php

namespace controller\graphql;

use controller\graphql\conf\conf;
use controller\graphql\nodes\operations\OperationNode;
use controller\graphql\nodes\QueryType;
use controller\graphql\nodes\types\union\BaseUnion;

class SchemaManager{

    private static ?SchemaManager $instance = null;
    private conf $conf;

    private function __construct(){
        $this->conf = new conf;
    }

    public static function getInstance(){
        if(is_null(self::$instance)){
            self::$instance = new SchemaManager();
        }
        return self::$instance;
    }

    /**
     * metodo che restituisce il nome completo di namespace della classe che rappresenta 
     * il tipo che ha nome $name nello schema
     * 
     * @param string $name nome del tipo per come dichiarato nello schema
     * @return string|null nome completo della classe che rappresenta il tipo, null se la classe non esiste
     */
    public function getTypeClassFromName(string $name): ?string{
        $className = ucfirst($name);
        $fullyQualifiedClassName = "controller\\graphql\\types\\{$className}Type";
        $res = class_exists($fullyQualifiedClassName) ? $fullyQualifiedClassName : null;
        return $res;
    }

    public function getOperation($operationName, QueryType $type): OperationNode {
        $typeValue = $type->value;
        $className = ucfirst($operationName);
        $fullyQualifiedClassName = "controller\\graphql\\nodes\\operations\\{$typeValue}\\{$className}Operation";
        return new $fullyQualifiedClassName();
    }

    public function getOperationResolver(OperationNode $operation)
    {
        $operationName = $operation->name;
        return $this->conf->$operationName();
    }

    public function operationReturnsUnion(OperationNode $operationNode): bool{
        return $operationNode->result instanceOf BaseUnion;
    }
}