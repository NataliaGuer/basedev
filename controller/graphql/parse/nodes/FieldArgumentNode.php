<?php

namespace controller\graphql\parse\nodes;

class FieldArgumentNode {
    public string $name;
    public $value;
    public ParameterNode $from;
}