<?php

namespace controller\graphql\schemaTypes;

enum query: string {
    case QUERY        = "query";
    case MUTATION     = "mutation";
    case SUBSCRIPTION = "subscription";
}