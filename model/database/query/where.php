<?php

namespace model\database\query;

enum where: string
{
    case EQUAL = "=";
    case GREATER_THAN_OR_EQUAL_TO = ">=";
    case LESS_THAN_OR_EQUAL_TO = "<=";
    case LESS_THAN = "<";
    case GREATER_THAN = ">";
    case NOT_EQUAL = "<>";
    case IN = "IN";
}