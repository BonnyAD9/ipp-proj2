<?php

namespace IPP\Student;

enum ErrorCode: int {
    case InvalidXml = 31;
    case BadXml = 32;
    case Semantic = 52;
    case BadOperand = 53;
    case NoVariable = 54;
    case NoFrame = 55;
    case NoValue = 56;
    case BadValue = 57;
    case StringError = 58;
    case IntegrationError = 88;
    case Other = 99;
}
