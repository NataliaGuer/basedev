<?php

namespace model\database\build;

use model\database\Connection;

class RelationshipBuilder extends EntityBuilder
{
    public function build($tableName)
    {
        $relInfo = $this->getTableRelationshipsInfo($tableName);
        $tableName = $this->convertTableName($tableName);
        foreach ($relInfo as $rel) {
            $this->writeRelationship($rel);
        }
    }

    public function getTableRelationshipsInfo($tableName)
    {
        $conn = Connection::getConnection();
        $sql = "
                SELECT
                    TABLE_NAME as tableName,
                    COLUMN_NAME as columnName,
                    REFERENCED_TABLE_NAME as referencedTableName,
                    REFERENCED_COLUMN_NAME as referencedColumnName
                FROM
                    INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE
                    TABLE_SCHEMA = 'rdb' AND TABLE_NAME = '$tableName' and REFERENCED_TABLE_SCHEMA IS NOT NULL
                ";
        $res = $conn->queryAndFetchAll($sql);
        return $res;
    }

    protected function writeRelationship($relInfo)
    {
        $tableName = $this->convertTableName($relInfo["tableName"]);
        $refTableName = $this->convertTableName($relInfo["referencedTableName"]);
        $relationshipName = "{$tableName}{$refTableName}Relationship";

        $file = "/var/www/rdb/model/relationship/$relationshipName.php";
        $this->clean($file);

        $start = $this->writeRelStart($relationshipName, $tableName, $refTableName);
        $properties = $this->writeProperties();
        $construct = $this->writeConstruct($tableName, $relInfo["columnName"], $refTableName, $relInfo["referencedColumnName"]);
        $getMethod = $this->writeGetMethod($relationshipName);
        $end = $this->writeEnd();

        $fs = fopen($file, "a+");
        fwrite($fs, $start);
        fwrite($fs, $properties);
        fwrite($fs, $construct);
        fwrite($fs, $getMethod);
        fwrite($fs, $end);

        fclose($fs);
    }

    protected function writeRelStart($relationshipName, $tableName, $refTableName)
    {
        $start = "<?php\n\n";
        $start .= "namespace model\\relationship;\n\n";
        $start .= "use model\\table\\{$tableName}Table;\n";
        $start .= "use model\\table\\{$refTableName}Table;\n";
        $start .= "\n";
        $start .= "class $relationshipName extends BaseRelationship\n{\n";
        return $start;
    }

    protected function writeProperties()
    {
        $prop = "\t" . 'private static $instance;' . "\n";
        $prop .= "\n";
        return $prop;
    }

    protected function writeConstruct($childTableName, $childTableColumn, $parentTableName, $parentTableColumn)
    {
        //la tabella in cui è contenuto il vincolo della chiave esterna è la child
        //la tabella referenziata è la parent
        $con = "\tprivate function __construct()\n\t{\n";
        $con .= "\t\t" . '$this->parentTable = ' . $parentTableName . 'Table::$tableName;' . "\n";
        $con .= "\t\t" . '$this->parentTableColumn = "' . $parentTableColumn . '";' . "\n";
        $con .= "\t\t" . '$this->childTable = ' . $childTableName . 'Table::$tableName;' . "\n";
        $con .= "\t\t" . '$this->childTableColumn = "' . $childTableColumn . '";' . "\n";
        $con .= "\t}\n\n";
        return $con;
    }

    protected function writeGetMethod($relationshipName)
    {
        $get = "\tpublic static function get()\n\t{\n";
        $get .= "\t\tif (self::".'$instance'." === null) {\n";
        $get .= "\t\t\t" . 'self::$instance = new ' . "$relationshipName();\n";
        $get .= "\t\t}\n";
        $get .= "\t\treturn self::".'$instance'.";\n\t}\n";
        return $get;
    }
}
