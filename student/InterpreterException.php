<?php

namespace IPP\Student;

use IPP\Core\Exception\IPPException;
use Throwable;

class InterpreterException extends IPPException {
    public function __construct(
        string $message = "",
        ErrorCode $code = ErrorCode::Other,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code->value, $previous);
    }
}
