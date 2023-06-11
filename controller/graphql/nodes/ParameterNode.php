<?php

namespace controller\graphql\nodes;

class ParameterNode {
    public string $name;
    public string $type;
    public string $alias;
    public bool $required;
    public $value;
}