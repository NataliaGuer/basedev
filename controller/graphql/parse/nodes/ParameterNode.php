<?php

namespace controller\graphql\parse\nodes;

class ParameterNode {
    public string $name;
    public string $type;
    public string $alias;
    public bool $required;
}