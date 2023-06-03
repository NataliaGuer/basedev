<?php

namespace controller\graphql\schemaTypes;

enum scalar: string {
    case INT     = "Int";
    case FLOAT   = "Float";
    case STRING  = "String";
    case BOOLEAN = "Boolean";
    case ID      = "ID";
}