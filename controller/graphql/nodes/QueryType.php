<?php

namespace controller\graphql\nodes;

enum QueryType: string {
    case QUERY        = "query";
    case MUTATION     = "mutation";
    case SUBSCRIPTION = "subscription";
}