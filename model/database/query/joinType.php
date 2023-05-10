<?php

namespace model\database\query;

enum joinType: string
{
    case INNER = "INNER JOIN";
    case LEFT = "LEFT JOIN";
    case RIGHT = "RIGHT JOIN";
}