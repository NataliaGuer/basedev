<?php

namespace model\table;

use Exception;
use model\database\Connection;
use model\database\query\joinType;
use model\database\query\order;
use model\database\query\statement;
use model\database\query\where;
use model\entity\BaseEntity;
use model\entitySet\BaseEntitySet;
use model\relationship\BaseRelationship;
use PDO;

abstract class BaseTable
{
    //infromazioni relative alla tabella stessa
    public static $tableName;
    public static $entityClass;
    public static $entitySetClass;
    protected static $primaryKey;

    //variabili necessarie alla costruzione della query
    /** @var statement */
    protected $statement;
    protected $wheres = [];
    /*
        wheres[] = [
                "tableName"  => tableName,
                "columnName" => columnName,
                "cond"       => cond,
                "value"      => value
            ]
    */
    protected $orderBy = [];
    /**
     * orderBy[] = [
     *      "tableName"  => tableName,
     *      "columnName" => columnName,
     *      "order"      => order
     * ]
     */
    protected $groupBy = [];
    protected $limit;
    protected $joins = [];
    /*
        joins["joinedTableName"] = [
            "jointype" => "",
            "relatedExternalColumn" => ""
            "relatedInternalColumn" => ""
        ]
    */
    protected $having = [];
    protected $fields = [];

    protected $connection;
    protected $queryTablesAliases;


    public function __construct()
    {
        $this->connection = Connection::getConnection();
    }

    public function select()
    {
        $this->statement = statement::SELECT;
        $this->fields[static::$tableName] = "*";
        return $this;
    }

    //alternativa alla select
    public function selectPage(int $pageSize, int $pageNumber)
    {
        $this->statement = statement::SELECT;

        //come per la select vengono selezionate di default tutte le colonne della tabella
        $this->fields[static::$tableName] = "*";
        $this->limit($pageSize, ($pageNumber-1)*$pageSize);
        return $this;
    }

    public function update(BaseEntity $entity)
    {
        $this->statement = statement::UPDATE;
        //inserire i campi dell'entità da aggiornare in un attributo di classe
        $this->fields = $entity->map();

        foreach (static::$primaryKey as $primaryKeyColumn) {
            $this->wheres[] = [
                "columnName" => $primaryKeyColumn,
                "value" => $this->fields[$primaryKeyColumn]
            ];

            //eliminazione delle colonne che costiruiscono la chiave primaria della tabella
            //dai fields
            unset($this->fields[$primaryKeyColumn]);
        }

        return $this->execute();
    }

    public function insert(BaseEntity $entity)
    {
        $this->statement = statement::INSERT;
        $map = $entity->map();
        $this->fields = array_filter($map, function ($value) {
            return $value !== null;
        });

        return $this->execute();
    }

    /**
     * metodo che si occupa di costruire e eseguire una query a partire dalle informazioni 
     * passate attraverso i metodi per la selezione del tipo di statement e delle condizioni
     * @return BaseEntity|BaseEntitySet|bool
     */
    public function execute(): BaseEntity|BaseEntitySet|bool
    {
        $statement = ucfirst(strtolower($this->statement->value));

        //ogni particolare tipo di statement viene gestito da un metodo dedicato
        $method = "execute{$statement}";
        $res = $this->$method();

        //dopo l'esecuzione della query tutte le informazioni necessarie per la sua esecuzione
        //vengono eliminate
        $this->clean();

        return $res;
    }

    protected function executeSelect()
    {
        $statement = $this->statement->value;

        $sql[] = $statement;

        //inserimento campi
        $fields = "";
        foreach ($this->fields as $tableName => $field) {
            $fields .= $tableName . "." . $field . ",";
        }
        $fields = substr($fields, 0, -1);
        $sql[] = $fields;

        $from = 'FROM ' . static::$tableName;
        if ($this->joins) {
            $joins = [];
            foreach ($this->joins as $key => $value) {
                $currentJoin = $value["joinType"]->value . " ";
                $relationship = $value["relationship"];
                if (static::$tableName === $relationship->parentTable) {
                    //se la tabella corrente è la parent devo joinare la child
                    //join relationship->childTable on relationship->parentTable.relationship->parentTableColumn = "relationship->childTable.relationship->childTableColumn"
                    $currentJoin .= $relationship->childTable;
                } else {
                    //se la tabella corrente è la child devo joinare la parent
                    //join relationship->parentTable on relationship->parentTable.relationship->parentTableColumn = "relationship->childTable.relationship->childTableColumn"
                    $currentJoin .= $relationship->parentTable;
                }
                $currentJoin .= " on " . $relationship->parentTable . "." . $relationship->parentTableColumn . "=" . $relationship->childTable . "." . $relationship->childTableColumn;
                $joins[] = $currentJoin;
            }
            $from .= implode(",", $joins);
        }
        $sql[] = $from;
        //array length?
        $values = [];
        if ($this->wheres && is_array($this->wheres)) {
            $wheres = [];
            foreach ($this->wheres as $key => $value) {
                //gestione della preparazione
                $columnName = $value["columnName"];
                //se la condizione è una in e come valore è stato passato un array
                $placeholder = "";
                if (is_array($value["value"]) && $value["cond"]->value === (where::IN)->value) {
                    $placeholders = [];
                    for ($i=0; $i < count($value["value"]); $i++) {
                        $inPlaceholder = ":in$i";
                        $placeholders[] = $inPlaceholder;
                        $values[$inPlaceholder] = $value["value"][$i];
                    }
                    $placeholder = implode(",", $placeholders);
                    $placeholder = "($placeholder)";
                } else {
                    $placeholder = ":$columnName";
                    $values[$columnName] = $value["value"];
                }
                $wheres[] =  $value["tableName"] . ".$columnName " . $value["cond"]->value . " $placeholder";
            }
            $wheres = implode(" AND ", $wheres);
            $where = "WHERE $wheres";
            $sql[] = $where;
        }

        if($this->groupBy){
            $groupBy = [];
            foreach ($this->groupBy as $groupCondition) {
                $tableName = $groupCondition["tableName"];
                $columnName = $groupCondition["columnName"];
                $groupBy[] = "$tableName.$columnName";
            }
            $groupBy = "GROUP BY " . implode(",", $groupBy);
            $sql[] = $groupBy;
        }

        if($this->orderBy){
            $orderBy = [];
            foreach ($this->orderBy as $orderCondition) {
                $tableName = $orderCondition["tableName"];
                $columnName = $orderCondition["columnName"];
                $order = $orderCondition["order"]->value;
                $orderBy[] = " $tableName.$columnName $order ";
            }
            $orderBy = "ORDER BY " . implode(",", $orderBy);
            $sql[] = $orderBy;
        }

        if($this->having){
            $having = [];
            foreach ($this->having as $havingCondition) {
                $tableName = $havingCondition["tableName"];
                $columnName = $havingCondition["columnName"];
                $having[] = " $tableName.$columnName " . $havingCondition["cond"]->value . " " . $havingCondition["value"];
            }
            $having = "HAVING " . implode(" AND ", $having);
            $sql[] = $having;
        }

        if($this->limit){
            $limit = "LIMIT ";
            $itemsNum = $this->limit["itemsNum"];
            if($this->limit["offset"] !== null){
                $offset = $this->limit["offset"];
                $limit .= "$offset, $itemsNum";
            } else {
                $limit .= "$itemsNum";
            }
            $sql[] = $limit;
        }

        $sql = implode(" ", $sql);

        $sth = $this->connection->prepare($sql);
        $sth->execute($values);
        $queryRes = $sth->fetchAll(PDO::FETCH_ASSOC);
        $res = false;
        if ($queryRes) {
            if (count($queryRes) > 1) {
                $res = new static::$entitySetClass();
                foreach ($queryRes as $row) {
                    $entity = new static::$entityClass();
                    $entity->set(...array_values($row));
                    $res->add($entity);
                }
            } else {
                $res = new static::$entityClass();
                $res->set(...array_values($queryRes[0]));
            }
        }

        return $res;
    }

    protected function executeInsert()
    {
        //INSERT INTO $tableName(...$fields) VALUES (:value1, :value2)

        $sql = [
            "INSERT INTO "
        ];

        // $this->fields[] = [
        //     'columnName' => 'value'
        // ]

        $tableFields = implode(",", array_keys($this->fields));
        $sql[] = static::$tableName . "($tableFields)";

        $values = [];
        foreach ($this->fields as $key => $value) {
            $values[] = ":$key";
        }

        $values = implode(", ", $values);
        $sql[] = "VALUES ($values)";

        $sql = implode(" ", $sql);

        $sth = $this->connection->prepare($sql);
        $sth->execute($this->fields);

        return (bool)$sth->rowCount();
    }

    protected function executeUpdate()
    {
        $sql = [
            $this->statement->value,
            static::$tableName,
            "SET"
        ];

        //$value contiene i valori da passare per l'esecuzione della query dopo la preparazione
        $values = $this->fields;

        $fieldNames = [];
        foreach ($this->fields as $key => $value) {
            $fieldNames[] = "`$key` =:$key";
        }
        //$fieldName = :$fieldName
        $sql[] = implode(", ", $fieldNames);

        //la condizione per l'aggiornamento deve essere posta sulle colonne che 
        //compongono la chiave primaria della tabella, non è detto che la chiave primaria sia composta 
        //da una sola colonna quindi mi posso aspettare che il metodo update abbia valorizzato l'attributo where
        //con più di un array
        $wheres = [];
        if ($this->wheres && is_array($this->wheres)) {
            foreach ($this->wheres as $key => $value) {
                //gestione della preparazione
                $columnName = $value["columnName"];
                $wheres[] = "`$columnName` =:$columnName";
                $values[$columnName] = $value["value"];
            }
        }
        $wheres = implode(" AND ", $wheres);
        $where = "WHERE $wheres";
        $sql[] = $where;

        $statement = implode(" ", $sql);
        $sth = $this->connection->prepare($statement);
        $sth->execute($values);

        //restituisce solo un indicazione sul successo della query
        return (bool)$sth->rowCount();
    }

    
    // il metodo delete, che si occupa di settare il giusto statement e le condizioni per la cancellazione della riga 
    // viene implemntata direttamente nella classe che rappresenta la tabella (classe che estende BaseTable) perchè,
    // a differenza delle insert, update e select, richiede che le vengano passati i valori corrispondenti alla chiave primaria della tabella
    // quindi il metodo avrà un numero diverso di argomenti per ogni Table
    protected function executeDelete()
    {
        $sql = [
            $this->statement->value,
            "FROM",
            static::$tableName
        ];
        
        $values = [];
        $wheres = [];
        if ($this->wheres && is_array($this->wheres)) {
            foreach ($this->wheres as $key => $value) {
                $columnName = $value["columnName"];
                $wheres[] = "`$columnName` =:$columnName";
                $values[$columnName] = $value["value"];
            }
        }
        $wheres = implode(" AND ", $wheres);
        $where = "WHERE $wheres";
        $sql[] = $where;

        $sql = implode(" ", $sql);
        $sth = $this->connection->prepare($sql);
        $sth->execute($values);

        return (bool)$sth->rowCount();
    }

    public function where(string $tableName, string $columnName, where $cond, mixed $value)
    {
        $this->wheres[] = [
            "tableName" => $tableName,
            "columnName" => $columnName,
            "cond" => $cond,
            "value" => $value
        ];

        return $this;
    }

    /**
     * metodo che consente di aggiungere una join alla query che si va costruendo
     * 
     * @param BaseRelationship $relationship classe che rappresenta la relazione tra la tabella corrente e un'altra tabella
     * @param joinType $joinType             il tipo di join da eseguire
     * 
     * @return BaseTable $this
     */
    public function join(BaseRelationship $relationship, joinType $joinType)
    {
        if (!array_key_exists($relationship::class, $this->joins)) {
            $this->joins[$relationship::class] = [
                "joinType"     => $joinType,
                "relationship" => $relationship
            ];
        }
        return $this;
    }

    public function groupBy(string $tableName, string $columnName)
    {
        $this->groupBy[] = [
            "tableName"  => $tableName,
            "columnName" => $columnName
        ];
        return $this;
    }

    public function having(string $tableName, string $columnName, where $whereCond, $value)
    {
        $this->having[] = [
            "tableName"  => $tableName,
            "columnName" => $columnName,
            "cond"       => $whereCond,
            "value"      => $value
        ];
    }

    public function orderBy(string $tableName, string $columnName, order $order)
    {
        $this->orderBy[] = [
            "tableName"  => $tableName,
            "columnName" => $columnName,
            "order"      => $order
        ];
        return $this;
    }

    public function limit(int $itemsNum, int $offset = null)
    {
        $this->limit = [
            "itemsNum"  => $itemsNum,
            "offset"    => $offset
        ];
    }

    protected function clean()
    {
        $this->statement = null;
        $this->wheres    = [];
        $this->orderBy   = [];
        $this->limit     = [];
        $this->joins     = [];
        $this->fields    = [];
    }
}
