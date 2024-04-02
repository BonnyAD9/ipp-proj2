<?php

namespace IPP\Student;

class Instruction {
    public string $opcode;
    /** @var array<int, Literal|string|Variable> */
    public array $args;

    public function __construct(
        string $opcode,
        /** @var array<int, Literal|string|Variable> */ array $args
    ) {
        $this->opcode = $opcode;
        $this->args = $args;
    }
}
