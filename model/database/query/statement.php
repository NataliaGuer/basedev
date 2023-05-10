<?php

namespace model\database\query;

enum statement: string
{
    case SELECT = "SELECT";
    case INSERT = "INSERT";
    case UPDATE = "UPDATE";
    case DELETE = "DELETE";
}