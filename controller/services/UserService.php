<?php

namespace controller\services;

use controller\graphql\nodes\operations\query\GetTestOperation;
use controller\graphql\nodes\operations\query\GetUserOperation;
use model\database\query\where;
use model\table\AddressTable;
use model\table\UserTable;

class UserService extends BaseService{

    protected UserTable $userTable;
    protected AddressTable $addressTable;

    public function __construct(){
        $this->userTable = new UserTable();
        $this->addressTable = new AddressTable();
    }
    
    public function getUser(GetUserOperation &$operation){
        $idParam = $operation->getParameterByName("id");
        $id = null;
        if($idParam){
            $id = $idParam->value;
        }
        $user = $this->userTable
                    ->select()
                    ->where(UserTable::$tableName, "id", where::EQUAL, $id)
                    ->execute();
        if($user){
            /** @var UserType $result */
            $result = $operation->result;
            $result->id         = $user->id;
            $result->givenName  = $user->name;
            $result->familyName = $user->surname;
        }
    }

    public function getTest(GetTestOperation &$operation){
        $idParam = $operation->getParameterByName("id");
        $id = null;
        if($idParam){
            $id = $idParam->value;
        }
        $address = $this->addressTable
                    ->select()
                    ->where(AddressTable::$tableName, "id", where::EQUAL, $id)
                    ->execute();
        if($address){
            $operation->result->castTo('address');
            /** @var TestUnion $result  */
            $result = $operation->result;
            $result->address->country = $address->country;
        }
    }
}
