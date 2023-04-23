<?php

namespace model\database\build;

use Exception;
use model\relationship\BaseRelationship;
use ReflectionClass;

/**
 * classe che si occupa della scrittura della classe che rappresenta la tabella
 */
class TableBuilder extends EntityBuilder
{
    protected $tableName;
    protected $relationships;
    public function build($tableName)
    {
        $plainTableName = $tableName;
        $columnsInfo = $this->getTableColumnsInfo($plainTableName);
        $tableName = $this->convertTableName($tableName);
        $fileName = "/var/www/rdb/model/table/{$tableName}Table.php";
        $this->clean($fileName);

        $this->relationships = $this->getRelationships($tableName);

        $start = $this->writeTableStart($tableName);

        $properties = $this->writeProperties($tableName, $plainTableName, $columnsInfo);

        //scrittura delle funzioni che permettono di joinare le tabelle con cui
        //la tabella corrente ha delle relazioni
        $joinFunctions = $this->writeJoinFunctions($plainTableName);
        $delete = $this->writeDelete($columnsInfo);
        $end = $this->writeEnd();

        $fs = fopen($fileName, "a+");
        if ($fs) {
            fwrite($fs, $start);
            fwrite($fs, $properties);
            fwrite($fs, $joinFunctions);
            fwrite($fs, $delete);
            fwrite($fs, $end);
            fclose($fs);
        } else {
            throw new Exception("Cannot open file $fileName");
        }
    }

    protected function writeTableStart(string $entityName)
    {
        $start = "<?php\n\n";
        $start .= "namespace model\\table;\n\n";
        $start .= "use model\\entity\\$entityName;\n";
        $start .= "use model\\entitySet\\" . $entityName . "Set;\n";
        $start .= "use model\\database\\query\\joinType;\n";
        $start .= "use model\\database\\query\\statement;\n";
        $start .= "use model\\database\\query\\where;\n";
        foreach ($this->relationships as $relationship) {
            $start .= "use model\\relationship\\$relationship;\n";
        }
        $start .= "\n";
        $comments = $this->getClassComments($entityName);
        $start .= $comments;
        $start .= "class " . $entityName . "Table extends BaseTable\n{\n";
        return $start;
    }

    //per la tabella user_test il tableName sarebbe UserTest
    protected function writeProperties($tableName, $plainTableName, $columnsInfo)
    {
        $prop = "\t" . 'public static $tableName = ' . "'$plainTableName';\n";
        $prop .= "\t" . 'public static $entityClass = ' . $tableName . "::class;\n";
        $prop .= "\t" . 'public static $entitySetClass = ' . $tableName . "Set::class;\n\n";

        if (is_array($columnsInfo)) {
            $primaryKey = "";
            foreach ($columnsInfo as $key => $column) {
                if (array_key_exists("primary", $column) && $column["primary"]) {
                    $name = $key;
                    $primaryKey .= "\"$name\",";
                }
            }
            //remove last char from string?
            $primaryKey = substr($primaryKey, 0, -1);
            $prop .= "\t" . 'protected static $primaryKey = ' . "[$primaryKey];\n\n";
        }
        return $prop;
    }

    protected function getRelationships($tableName)
    {
        $relationships = [];
        $relPath = "/var/www/rdb/model/relationship";
        $totalRel = scandir($relPath);
        try {
            if ($totalRel && is_array($totalRel)) {
                foreach ($totalRel as $rel) {
                    if (str_contains($rel, $tableName)) {
                        $relationships[] = str_replace(".php", "", $rel);
                    }
                }
            }
        } catch (\Throwable $th) {
            echo ($th->getMessage());
        }
        return $relationships;
    }

    public function getRelationshipClassName(array $needle)
    {
        $rel = array_filter($this->relationships, function ($rel) use ($needle) {
            $res = true;
            foreach ($needle as $n) {
                $res = str_contains(strtolower($rel), $n);
            }
            return $res;
        });
        return current($rel);
    }

    protected function writeJoinFunctions($plainTableName)
    {
        $joinFunctions = "";
        $rb = new RelationshipBuilder();
        $tableRelationshipsInfo = $rb->getTableRelationshipsInfo($plainTableName);
        if ($tableRelationshipsInfo) {
            foreach ($tableRelationshipsInfo as $rel) {
                //ottenere le relazioni che contengono nome della tabella e nome della tabella joinata
                //la joinedtable è la tabella padre
                $joinedTable = $rel['referencedTableName'];
                $joinedTableClassName = $this->convertTableName($joinedTable);
                $relationshipClassName = $this->getRelationshipClassName([$plainTableName, $joinedTable]);
                if ($relationshipClassName) {
                    $joinFunctions .= "\tpublic function join" . $joinedTableClassName . '(joinType $joinType)' . "\n\t{\n";
                    $joinFunctions .= "\t\t" . '$this->join(' . $relationshipClassName . '::get(), $joinType);' . "\n";
                    $joinFunctions .= "\t\t" . 'return $this;' . "\n";
                    $joinFunctions .= "\t}\n\n";
                }
            }
        }
        return $joinFunctions;
    }

    protected function writeDelete($columnsInfo)
    {
        $delete = "";
        if (is_array($columnsInfo)) {
            $primaryKeys = "";
            $pr = [];
            foreach ($columnsInfo as $key => $column) {
                if (array_key_exists("primary", $column) && $column["primary"]) {
                    $name = $this->convertColumnName($key);
                    $pr[] = $name;
                    $primaryKeys .= "\$$name, ";
                }
            }
            $primaryKeys = substr($primaryKeys, 0, -2);
            $delete = "\tpublic function delete($primaryKeys)\n\t{\n";
            $delete .= "\t\t\$this->statement = statement::DELETE;\n";
            foreach ($pr as $key => $name) {
                $delete .= "\t\t\$this->where(self::\$tableName, self::\$primaryKey[$key], where::EQUAL, \$$name);\n";
            }

            $delete .= "\t\treturn \$this->execute();\n";
            $delete .= "\t}\n";
        }
        return $delete;
    }

    protected function getClassComments($entityName)
    {
        $comments = "/**\n";
        $comments .= " * @method $entityName|{$entityName}Set|bool execute()\n";
        $comments .= " */\n";
        return $comments;
    }
}
