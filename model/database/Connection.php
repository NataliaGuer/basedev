<?php

namespace model\database;

use PDO;

/**
 * Singleton per la connessione al DB e l'esecuzione di query
 */
class Connection
{

    protected static $connection;
    protected $pdo;
    protected $dsn;

    protected function __construct()
    {
        $options = [
            PDO::ATTR_EMULATE_PREPARES   => false, // Disable emulation mode for "real" prepared statements
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Disable errors in the form of exceptions
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Make the default fetch be an associative array
        ];

        $this->dsn = $this->getDsn();

        $this->pdo = new PDO($this->dsn, 'nan', 'Nat16110ni1999#', $options);
    }


    public static function getConnection(): Connection
    {
        if (self::$connection === null) {
            self::$connection = new Connection();
        }
        return self::$connection;
    }

    public function __call($name, $arguments){
        return $this->pdo->$name(...$arguments);
    }

    public function query($sql)
    {
        return $this->pdo->query($sql);
    }

    public function queryAndFetchAll($sql){
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function queryAndFetchToClass($sql, $class)
    {
        $sth = $this->pdo->query($sql);
        $sth->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, $class);
        $result = $sth->fetch();
        return $result;
    }

    public function closeConnection()
    {
        self::$connection = null;
    }

    private function getDsn(): string
    {
        //inserire logica per leggere da file di configurazione
        return "mysql:host=127.0.0.1;dbname=rdb";
    }
}
