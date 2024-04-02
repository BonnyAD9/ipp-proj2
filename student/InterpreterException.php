<?php

namespace IPP\Student;

use Exception;
use Throwable;

class InterpreterException extends Exception {
    public function __construct(
        string $message = "",
        int $code = 99,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
