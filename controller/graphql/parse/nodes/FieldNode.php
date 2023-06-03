<?php

namespace controller\graphql\parse\nodes;

class FieldNode {
    public string $name;
    /** @var FieldNode[] */
    public array $fields;
    /** @var FieldArgumentNode[] */
    public array $arguments;
}