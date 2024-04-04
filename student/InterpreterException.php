<?php

namespace IPP\Student;

use IPP\Core\Exception\IPPException;
use Throwable;

class InterpreterException extends IPPException {
    public function __construct(
        string $message = "",
        int $code = 99,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
