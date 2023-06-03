<?php

namespace controller\graphql\operations;

class OperationMapper {

    protected static $map = [
        'getUser' => GetUserOperation::class
    ];

    public static function map($operationName): string{
        return self::$map[$operationName];
    }
}