<?php

namespace controller\graphql\operations;

abstract class BaseOperation {
    
    public string $name;
    
    //settato nel costruttore
    protected OperationType $type;
    //riscritta dalla classe che estende
    //settato nel costruttore
    protected string $returnType;
    //contiene l'associazione nome-tipo dei parametri richiesti
    protected array $requiredParams;
    
    //settate in un secondo momento rispetto alla costruzione
    protected array $variables;
    //array contenente i nomi dei campi dell'entita da ritornare
    protected array $fieldsToReturn;
    //array contenente i nomi dei parametri per come sono stati passati da front-end
    protected array $paramAliases;

    public function setFieldsToReturn(array $fields){
        $this->fieldsToReturn = $fields;
    }

    public function getFieldsToReturn(): array{
        return $this->fieldsToReturn;
    }
    
    public function setParamAlias($paramName, $alias){
        //controllare il parametro sia veramente associato alla query
        if(array_key_exists($paramName, $this->requiredParams)){
            $this->paramAliases[$paramName] = $alias;
        }
    }
    
    public function setVariables(array $variables){
        //variables contiene alias => valore
        //le variabili vengono filtrate in base al valore di $inputParams
        foreach ($variables as $key => $value) {
            if(in_array($key, $this->paramAliases)){
                $this->variables[$key] = $value;
            }
        }
    }

    public function getVariable(string $name){
        return $this->variables[$this->paramAliases[$name]];
    }
}