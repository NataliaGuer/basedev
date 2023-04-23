<?php

namespace model\database\build;

use model\database\Connection;
use model\entity\BaseEntity;

class EntityBuilder
{

    protected $typeMapper = [
        "string" => [
            "CHAR",
            "VARCHAR",
            "BINARY",
            "VARBINARY",
            "TINYBLOB",
            "TINYTEXT",
            "TEXT",
            "BLOB",
            "MEDIUMTEXT",
            "MEDIUMBLOB",
            "LONGTEXT",
            "LONGBLOB"
        ],
        "int" => [
            "BIT",
            "TINYINT",
            "SMALLINT",
            "MEDIUMINT",
            "INT",
            "INTEGER",
            "BIGINT",
        ],
        "bool" => [
            "BOOLEAN",
            "BOOL"
        ],
        "date" => [
            "DATE",
            "DATETIME",
            "TIMESTAMP"
        ],
        "array" => [
            "ENUM",
            "SET"
        ],
        "float" => [
            "FLOAT",
            "DOUBLE",
            "DECIMAL",
            "DEC"
        ]
    ];

    public function build($tableName)
    {
        //eliminazione dell'eventuale entity creata in precedenza
        $className = $this->convertTableName($tableName);
        $fileName = '/var/www/rdb/model/entity/' . $className . '.php';
        $this->clean($fileName);

        //dichiarazione namespace e nome classe
        $start = $this->writeStart($className);

        //ottenimento informazioni per dichiarazione parametri
        $columns = $this->getTableColumnsInfo($tableName);
        $properties = [];
        if (is_array($columns)) {
            foreach ($columns as $name => $column) {
                $columnsAsProperties = [];
                $type = "";
                foreach ($this->typeMapper as $phpType => $mySqlTypes) {
                    if (in_array(strtoupper($column["type"]), $mySqlTypes)) {
                        $type = $phpType;
                    }
                }
                $columnsAsProperties = [
                    "type"       => $type,
                    "name"       => $this->convertColumnName($name),
                    "columnName" => $name,
                    "primary"    => array_key_exists("primary", $column) ? $column["primary"] : false
                ];
                if (array_key_exists("comment", $column)) {
                    $columnsAsProperties["comment"] = $column["comment"];
                }
                $properties[] = $columnsAsProperties;
            }
        }

        //dichiarazione variabili
        $variableDeclaration = $this->writeVariablesDeclaration($properties);

        $attributeToColumnMapper = $this->writeAttributeToColumnMapper($properties);

        //scrittura metodo che permette di settare tutte le proprietà della classe
        $setter = $this->writeMethod("set", $properties, $this->getSetterBody($properties));

        //scrittura metodi necessari per estendere BaseEntity
        $baseMethods = $this->writeBaseMethods($properties);

        $end = $this->writeEnd();


        //apertura file
        $fs = fopen($fileName, 'a+');

        if ($fs) {
            fwrite($fs, $start);
            fwrite($fs, $variableDeclaration);
            fwrite($fs, $attributeToColumnMapper);
            fwrite($fs, $setter);
            fwrite($fs, $baseMethods);
            fwrite($fs, $end);

            fclose($fs);
        }
    }

    protected function clean($fileName)
    {
        if (file_exists($fileName)) {
            unlink($fileName);
        }
    }

    protected function writeStart(string $className)
    {
        $start = "<?php\n\n";
        $start .= "namespace model\\entity;\n\n";
        $start .= "class $className extends BaseEntity\n{\n";
        return $start;
    }

    protected function writeVariablesDeclaration($columns)
    {
        $dec = "\n";
        foreach ($columns as $column) {
            if (array_key_exists("comment", $column)) {
                $comment = $column["comment"];
                $type = $column["type"];
                $dec .= "\t/** @var $type $comment*/\n";
            }
            $name = $column["name"];
            $dec .= "\tpublic $$name;\n";
        }
        return $dec;
    }

    protected function writeAttributeToColumnMapper($properties){
        $res = "";
        if ($properties) {
            $res = "\n\tprotected static \$attributeToColumnMapper = [";
            foreach ($properties as $property) {
                $attributeName = $property["name"];
                $columnName = $property["columnName"];
                $res .= "\n\t\t\"$attributeName\" => \"$columnName\",";
            }
            $res = substr($res, 0, -1);
            $res .= "\n\t];\n";
        }
        return $res;
    }

    protected function writeMethod($methodName, $params, $methodBody)
    {
        $method = null;
        $method = "\n\tpublic function $methodName(";
        if ($params && is_array($params)) {
            foreach ($params as $param) {
                $type = $param['type'];
                $name = $param['name'];

                $method .= "$type $$name, ";
            }
            $method = substr($method, 0, -2);
        }
        $method .= ")\n\t{\n";
        $method .= $methodBody;
        $method .= "\t}\n";
        return $method;
    }

    protected function getSetterBody($params)
    {
        $body = "";
        foreach ($params as $param) {
            $name = $param['name'];
            $body .= "\t\t\$this->$name = $$name;\n";
        }
        return $body;
    }

    protected function writeEnd()
    {
        $end = "}";
        return $end;
    }

    public function getTableColumnsInfo($tableName)
    {
        $conn = Connection::getConnection();
        $sql = "SELECT column_name as name, is_nullable as nullable, data_type as `type`, column_key as `key`, column_comment as comment
                from information_schema.columns where table_name = '$tableName' and TABLE_SCHEMA = 'rdb' ORDER BY ORDINAL_POSITION";
        $res = $conn->query($sql);
        $columns = [];
        foreach ($res as $column) {
            $data = [
                "type" => $column["type"],
                "is_nullable" => $column["nullable"] === 'YES'
            ];
            if ($column["key"] == 'PRI') {
                $data['primary'] = true;
            }
            if ($column["comment"]) {
                $data['comment'] = $column["comment"];
            }
            $columns[$column["name"]] = $data;
        }
        return $columns;
    }

    protected function writeBaseMethods($params)
    {
        //scrittura metodo getKey
        $baseMethods = get_class_methods(BaseEntity::class);
        $baseMethodsImpl = [];
        //vengono richiamati tutti i metodi di questa classe per la restituzione del corpo dei
        //metodi di base di BaseEntity
        if ($baseMethods && is_array($baseMethods)) {
            foreach ($baseMethods as $method) {
                $methodImplGetter = $method . "MethodImpl";
                if (method_exists($this, $methodImplGetter)) {
                    $baseMethodsImpl[] = $this->$methodImplGetter($params);
                }
            }
        }
        $baseMethods = "";
        if ($baseMethodsImpl) {
            $baseMethods = implode("\n", $baseMethodsImpl);
        }
        return $baseMethods;
    }

    protected function getKeyMethodImpl($params)
    {
        $primaryKeys = [];
        foreach ($params as $param) {
            if (array_key_exists("primary", $param) && $param["primary"]) {
                $primaryKeys[] = $param;
            }
        }
        $getKeyImpl = "";
        if ($primaryKeys) {
            $getKeyBody = "";
            $getKeyBody = "\t\treturn ";
            foreach ($primaryKeys as $primaryKey) {
                $getKeyBody .= '$this->' . $primaryKey["name"] . '."_".';
            }
            $getKeyBody = substr($getKeyBody, 0, -5);
            $getKeyBody .= ";\n";
            $getKeyImpl = $this->writeMethod("getKey", null, $getKeyBody);
        }
        return $getKeyImpl;
    }

    /**
     * funzione di utilità che converte il nome della colonna da snakecase in camelcase con la prima lettera minuscola
     * test_column -> testColumn
     * 
     * @param  string $columnName
     * @return string
     */
    protected function convertColumnName(string $columnName): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $columnName))));
    }


    /**
     * funzione di utilità che converte il nome della tabella da snakecase in camelcase con la prima lettera maiuscola
     * test_table -> TestTable
     *
     * @param  string $tableName
     * @return string
     */
    protected function convertTableName(string $tableName): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $tableName)));
    }
}
