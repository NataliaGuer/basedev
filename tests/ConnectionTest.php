<?php

use model\database\Connection;
use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase
{

    public function testConnection()
    {
        $connection = Connection::getConnection();
        //table user(id, name)
        $expected = [
            "id" => 1,
            "name" => "admin",
            "surname" => "test"
        ];
        $this->assertEquals($expected, $connection->query("SELECT * from user where id = 1"));
    }
}
