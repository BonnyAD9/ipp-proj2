<?php

namespace IPP\Student;

class Frame {
    /**
     *@var array<string, Variable>
     */
    public array $variables;

    public function __construct() {
        $this->variables = [];
    }

    public function getVariable(string $name) {
        return $this->variables[$name];
    }

    public function setVariable(Variable $value) {
        $this->variables[$value->name] = $value;
    }
}
