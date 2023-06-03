<?php

namespace controller\graphql\parse;

use controller\graphql\operations\BaseOperation;
use controller\graphql\operations\OperationMapper;
use controller\graphql\parse\nodes\FieldArgumentNode;
use controller\graphql\parse\nodes\FieldNode;
use controller\graphql\parse\nodes\OperationNode;
use controller\graphql\parse\nodes\ParameterNode;
use controller\graphql\parse\nodes\QueryNode;
use controller\graphql\schemaTypes\query;
use PhpParser\Node\Stmt\Foreach_;

class Parser
{
    private QueryNode $queryNode;

    public function parseQuery($query)
    {
        $this->queryNode = new QueryNode();
        $res = $this->queryNode;
        try {

            $query = trim($query);

            $this->queryNode->type = $this->parseType($query);
            $this->queryNode->name = $this->parseName($query);
            $this->queryNode->parameters = $this->parseParameters($query);

            $this->queryNode->operations = $this->parseOperations($query);

        } catch (\Throwable $th) {
            $res = false;
        }
        return $res;
    }

    public function parseOperation(string $name, array $data): BaseOperation
    {
        $operationClass = OperationMapper::map($name);
        /** @var BaseOperation */
        $operation = new $operationClass();
        foreach ($data["parameters"] as $name => $paramInfo) {
            $operation->setParamAlias($name, $paramInfo["as"]);
        }
        $operation->setFieldsToReturn($data["fieldsToReturn"]);
        return $operation;
    }

    protected function parseType(&$query)
    {
        $res = query::QUERY;
        foreach (query::cases() as $type) {
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

    protected function parseOperations($query){
        $res = [];
        $body = $this->getBody($query);
        $operations = $this->extractOperations($body);
        foreach ($operations as $operation) {
            $operationNode = new OperationNode();
            $operationNode->name = $this->parseName($operation);
            $operationNode->parameters = $this->parseParameters($operation);
            $operationNode->fieldsToReturn = $this->parseOperationFields($this->getBody($operation));
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
        $fieldNode->name = $fieldName;
        if ($arguments) {
            $fieldNode->arguments = $arguments;
        }
        return $fieldNode;
    }
}
