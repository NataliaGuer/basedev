<?php

namespace controller\graphql\services;

use controller\graphql\operations\GetUserOperation;
use controller\graphql\types\BaseType;
use controller\graphql\types\UserType;
use model\database\query\where;
use model\table\UserTable;

class UserService extends BaseService{

    protected UserTable $userTable;

    public function __construct(){
        $this->operationToMethod = [
            GetUserOperation::class => 'getUser'
        ];
        $this->userTable = new UserTable();
    }
    
    protected function getUser(GetUserOperation $operation): BaseType {
        $id = $operation->getVariable("id");
        $user = $this->userTable
                    ->select()
                    ->where(UserTable::$tableName, "id", where::EQUAL, $id)
                    ->execute();
        $res = new UserType();
        $res->constructFromModel($user);
        return $res;
    }
}
