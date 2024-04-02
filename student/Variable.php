<?php

namespace IPP\Student;

class Variable {
    public FrameType $frame;
    public string $name;

    public function __construct(FrameType $frame, string $name) {
        $this->frame = $frame;
        $this->name = $name;
    }
}
