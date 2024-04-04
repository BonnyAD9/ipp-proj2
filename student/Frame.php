<?php

namespace IPP\Student;

class Frame {
    /** @var array<string, Literal> */
    public array $variables;

    public function __construct() {
        $this->variables = [];
    }

    public function getVariable(string $name): Literal {
        if (isset($this->variables[$name])) {
            if ($this->variables[$name]->type == VarType::Unset) {
                throw new InterpreterException(
                    "Cannot read variable ".$name.". It has undefined value.",
                    ErrorCode::NoValue
                );
            }
            return $this->variables[$name];
        }
        throw new InterpreterException(
            "Cannot read variable ".$name.". There is no such variable",
            ErrorCode::NoVariable
        );
    }

    public function setVariable(string $name, Literal $value): void {
        if (!isset($this->variables[$name])) {
            throw new InterpreterException(
                "Cannot set variable '".$name."'. The variable doesn't exist.",
                ErrorCode::NoVariable
            );
        }
        $this->variables[$name] = $value;
    }

    public function declVariable(string $name): void {
        if (isset($this->variables[$name])) {
            throw new InterpreterException(
                "Cannot declare variable '".$name."'. It is already declared.",
                ErrorCode::Semantic
            );
        }
        $this->variables[$name] = (new Literal(null))->unset();
    }

    public function isDecl(string $name): bool {
        return isset($this->variables[$name])
            && $this->variables[$name]->type != VarType::Unset;
    }
}
