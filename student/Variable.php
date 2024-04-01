<?php

namespace IPP\Student;

class Variable {
    public string $name;
    public VarType $type;
    public null|int|string|bool $value;

    public function __construct(string $name, null|int|string|bool $value) {
        $this->name = $name;
        $this->value = $value;

        if (is_int($value)) {
            $this->type = VarType::Int;
        } else if (is_string($value)) {
            $this->type = VarType::String;
        } else if (is_bool($value)) {
            $this->type = VarType::Bool;
        } else {
            $this->type = VarType::Nil;
        }
    }
}
