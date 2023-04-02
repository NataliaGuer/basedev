<?php

namespace model\database;

use PDO;

/**
 * Singleton per la connessione al DB
 */
class Connection {

    protected static $connection;
    protected $pdo;
    protected $dsn;

    protected function __construct()
    {   
        $this->dsn = $this->getDsn();

        $options = [
            PDO::ATTR_EMULATE_PREPARES   => false, // Disable emulation mode for "real" prepared statements
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Disable errors in the form of exceptions
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Make the default fetch be an associative array
        ];

        $this->pdo = new PDO($this->dsn, 'nan', 'Nat16110ni1999#', $options);
    }

    
    public static function getConnection(){
        if(self::$connection === null){
            self::$connection = new Connection();
        }
        return self::$connection;
    }

    public function query($sql){
        $result = $this->pdo->query($sql)->fetch();
        return $result;
    }
    
    public function closeConnection(){
        self::$connection = null;
    }

    private function getDsn(){
        return "mysql:host=127.0.0.1;dbname=rdb";
    }
}