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
            if ($this->variables[$name]->type == VarType::Unset) {
                throw new InterpreterException(
                    "Cannot read variable ".$name.". It has undefined value.",
                    56
                );
            }
            return $this->variables[$name];
        }
        throw new InterpreterException(
            "Cannot read variable ".$name.". There is no such variable",
            54
        );
    }

    public function setVariable(string $name, Literal $value) {
        if (!isset($this->variables[$name])) {
            throw new InterpreterException(
                "Cannot set variable '".$name."'. The variable doesn't exist.",
                54
            );
        }
        $this->variables[$name] = $value;
    }

    public function declVariable(string $name) {
        if (isset($this->variables[$name])) {
            throw new InterpreterException(
                "Cannot declare variable '".$name."'. It is already declared.",
                52
            );
        }
        $this->variables[$name] = (new Literal(null))->unset();
    }

    public function isDecl(string $name): bool {
        return isset($this->variables[$name])
            && $this->variables[$name]->type != VarType::Unset;
    }
}
