<?php

namespace controller\graphql\operations;

enum OperationType: string {
    case QUERY = "QUERY";
    case MUTATION = "MUTATION";
    case SUBSCRIPTION = "SUBSCRIPTION";
}