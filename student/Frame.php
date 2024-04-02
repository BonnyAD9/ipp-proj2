<?php

namespace IPP\Student;

class Frame {
    /** @var array<string, Literal> */
    public array $variables;

    public function __construct() {
        $this->variables = [];
    }

    public function getVariable(string $name) {
        if (isset($this->variables[$name])) {
            return $this->variables[$name];
        }
        throw new InterpreterException(
            "Cannot read variable ".$name.". There is no such variable",
            54
        );
    }

    public function setVariable(string $name, Literal $value) {
        $this->variables[$name] = $value;
    }
}
