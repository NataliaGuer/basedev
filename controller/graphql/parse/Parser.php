<?php

namespace controller\graphql\parse;

use controller\graphql\nodes\FieldArgumentNode;
use controller\graphql\nodes\FieldNode;
use controller\graphql\nodes\operations\OperationNode;
use controller\graphql\nodes\ParameterNode;
use controller\graphql\nodes\QueryNode;
use controller\graphql\nodes\QueryType;
use controller\graphql\nodes\types\union\BaseUnion;
use controller\graphql\SchemaManager;

class Parser
{
    private QueryNode $queryNode;
    private SchemaManager $schemaManager;

    public function parseQuery(string $query, array $variables)
    {
        $this->queryNode = new QueryNode();
        $this->schemaManager = SchemaManager::getInstance();
        $res = $this->queryNode;
        try {

            $query = trim($query);

            $this->queryNode->type = $this->parseType($query);
            $this->queryNode->name = $this->parseName($query);
            $parameters = $this->parseParameters($query);
            $this->setQueryParametersValue($variables, $parameters);
            $this->queryNode->parameters = $parameters;

            $this->queryNode->operations = $this->parseOperations($query);

        } catch (\Throwable $th) {
            $res = false;
        }
        return $res;
    }

    protected function parseType(&$query)
    {
        $res = QueryType::QUERY;
        foreach (QueryType::cases() as $type) {
            if (strpos($query, $type->value) !== false) {
                $res = $type;
                $query = substr_replace($query, "", 0, strlen($res->value));
                break;
            }
        }
        return $res;
    }

    protected function parseName(&$query)
    {
        $query = trim($query);
        $name = trim(substr($query, 0, strpos($query, "("))) ?: trim(substr($query, 0, strpos($query, "{")));
        $query = substr_replace($query, "", 0, strlen($name));
        return $name;
    }

    protected function parseParameters(&$query)
    {
        $query = trim($query);
        $res = [];
        $rawParams = trim(substr($query, 0, strpos($query, "{")));
        if ($rawParams) {
            $params = str_replace(" ", "", substr($rawParams, strpos($rawParams, "(") + 1, -1));
            $params = explode(",", $params);
            foreach ($params as $arg) {
                $parts = explode(":", $arg);
                $paramNode = new ParameterNode();
                //dipendentemente dal fatto che si stiano parsando gli argomenti della query o delle sue operazioni avremo due casi:
                //1. query -> parametri nella forma ($alias: type) -> vengono settati: alias, tipo e required
                //2. operazione -> parametri nella forma (name: $alias) -> vengono settati: nome e alias
                $alias = null;
                if (strpos($parts[0], "$") !== false) {
                    $alias = $parts[0];
                    $paramNode->required = strpos($parts[1], "!") !== false;
                    $paramNode->type = trim(str_replace([" ", "!"], "", $parts[1]));
                } else {
                    $alias = $parts[1];
                    $paramNode->name = trim($parts[0]);
                }
                $paramNode->alias = trim(str_replace("$", "", $alias));
                $res[] = $paramNode;
            }
            $query = substr_replace($query, "", 0, strlen($rawParams));
        }
        return $res;
    }

    protected function setQueryParametersValue(array $variables, array &$parameters){
        foreach ($parameters as $parameter) {
            if(array_key_exists($parameter->alias, $variables)){
                $parameter->value = $variables[$parameter->alias];
            }
        }
    }

    protected function parseOperations($query){
        $res = [];
        $body = $this->getBody($query);
        $operations = $this->extractOperations($body);
        foreach ($operations as $operation) {
            $operationName = $this->parseName($operation);
            $operationNode = $this->schemaManager->getOperation($operationName, $this->queryNode->type);
            $operationNode->parameters = $this->parseOperationParameters($operation);
            //differenziare comportamento -> standard, unioni
            $operationBody = $this->getBody($operation);
            if($this->schemaManager->operationReturnsUnion($operationNode)){
                //array dei campi che devono essere restituiti per ogni possibile tipo dell'unione
                $fieldsToReturn = $this->parseOperationFieldsByType($operationBody);
                foreach ($fieldsToReturn as $typeName => $fields) {
                    /** @var BaseUnion $result */
                    $result = $operationNode->result;
                    $result->setFieldsToReturn($fields, $typeName);
                }
            } else {
                $operationNode->result->fieldsToReturn = $this->parseOperationFields($operationBody);
            }
            $res[] = $operationNode;
        }
        return $res;
    }

    protected function getBody($query)
    {
        return trim(substr($query, strpos($query, "{") + 1, -1));
    }

    protected function extractOperations($body)
    {

        $operations = [];

        $i = 0;
        $skip = 0;
        $start = 0;
        while (strlen($body)) {
            if ($body[$i] === "}") {
                if ($skip == 0) {
                    if (isset($body[$i + 1]) && $body[$i + 1] === ",") {
                        $body[$i + 1] = " ";
                    }
                    $operation = substr($body, 0, $i + 1);
                    $operations[] = trim($operation);
                    $body = trim(str_replace($operation, "", $body));
                    $start = $i = 0;
                } else {
                    $skip--;
                }
            } else if ($body[$i] === "{") {
                if ($start == 0) {
                    $start = $i;
                } else {
                    $skip++;
                }
            }
            $i++;
        }
        return $operations;
    }

    protected function parseOperationParameters($operation){
        $parameters = $this->parseParameters($operation);
        foreach ($parameters as &$parameter) {
            if(!$parameter->value){
                //si prendono dal queryNode tutti i parametri dell'operazione che fanno riferimento
                //a un parametro passato alla query
                $queryParameter = $this->queryNode->getParameterFromAlias($parameter->alias);
                $parameter->value = $queryParameter->value;
            }
        }
        return $parameters;
    }

    protected function parseOperationFields($operation)
    {
        $res = [];
        $rawFields = str_replace([" ", "\n"], "", $operation);

        $insideFieldArgs = false;
        $i = 0;
        while (strlen($rawFields) && $i < strlen($rawFields)) {
            switch ($rawFields[$i]) {
                case '(':
                    $insideFieldArgs = true;
                    break;
                case ')':
                    $insideFieldArgs = false;
                    break;
                case ',':
                    if(!$insideFieldArgs){
                        $field = substr($rawFields, 0, $i);
                        $rawFields = substr_replace($rawFields, "", 0, strlen($field) + 1);
                        $i = 0;
                        $fieldNode = $this->parseField($field);
                        $res[] = $fieldNode;
                    }
                    break;
                case '{':
                    $field = substr($rawFields, 0, $i);
                    $fieldContent = null;
                    $finished = false;
                    $j = $i;
                    $skip = 0;
                    $start = 0;
                    while (!$finished) {
                        if ($rawFields[$j] === "}") {
                            if ($skip === 0) {
                                $fieldContent = substr($rawFields, $i + 1, $j - $i - 1);
                                $finished = true;
                            } else {
                                $skip--;
                            }
                        } else if ($rawFields[$j] === "{") {
                            if ($start == 0) {
                                $start = $i;
                            } else {
                                $skip++;
                            }
                        }
                        $j++;
                    }
                    $fieldNode = $this->parseField($field);
                    $fieldNode->fields = $this->parseOperationFields($fieldContent);
                    $res[] = $fieldNode;
                    $toRemove = "$field\x7B$fieldContent\x7D";
                    $rawFields = substr_replace($rawFields, "", 0, strlen($toRemove) + 1);
                    $i = 0;
                    break;
                default:
                    break;
            }
            $i++;
        }

        if (strlen($rawFields)) {
            $res[] = $this->parseField($rawFields);
        }

        return $res;
    }

    public function parseOperationFieldsByType($operation){
        $cases = $this->extractOperations($operation);
        $fieldsDividedByType = [];
        foreach ($cases as $case) {
            $on = trim(str_replace("...on", "", $this->parseName($case)));
            $fields = $this->parseOperationFields($this->getBody($case));
            $fieldsDividedByType[$on] = $fields;
        }
        return $fieldsDividedByType;
    }

    protected function parseField($field){
        $fieldNode = new FieldNode();
        $fieldName = $field;
        $arguments = [];
        if (($i = strpos($field, "(")) !== false && ($j = strpos($field, ")")) !== false) {
            $fieldName = substr($field, 0, $i);
            $stringParams = substr($field, $i + 1, $j - $i - 1);
            $arrayParams = explode(",", $stringParams);
            foreach ($arrayParams as $param) {
                //controllare se il valore è una variabile
                $param = explode(":", $param);
                $name = trim($param[0]);
                $value = trim($param[1]);
                //se il valore dell'argomento è una variabile
                $arg = new FieldArgumentNode();
                $arg->name = $name;
                if (($x = strpos($value, "$")) !== false) {
                    $arg->from = $this->queryNode->getParameterFromAlias(substr($value, $x+1));
                } else {
                    $arg->value = $value;
                }
                $arguments[] = $arg;
            }
        }
        //il campo ha un alias quindi il suo name è alias:name
        if (strpos($fieldName, ":") !== false) {
            $fieldNameArray = explode(":", $fieldName);
            $fieldNode->toReturnAs = $fieldNameArray[0];
            $fieldName = $fieldNameArray[1];
        }
        $fieldNode->name = $fieldName;
        if ($arguments) {
            $fieldNode->arguments = $arguments;
        }
        return $fieldNode;
    }
}
