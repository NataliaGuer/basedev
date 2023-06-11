<?php

namespace controller\graphql\nodes;

class FieldArgumentNode {
    public string $name;
    public $value;
    public ParameterNode $from;
}