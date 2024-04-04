<?php

namespace IPP\Student;

enum OpCode: string {
    case Move = "MOVE";
    case CreateFrame = "CREATEFRAME";
    case PushFrame = "PUSHFRAME";
    case PopFrame = "POPFRAME";
    case DefVar = "DEFVAR";
    case Call = "CALL";
    case Return = "RETURN";
    case PushS = "PUSHS";
    case PopS = "POPS";
    case Add = "ADD";
    case Sub = "SUB";
    case Mul = "MUL";
    case IDiv = "IDIV";
    case Lt = "LT";
    case Gt = "GT";
    case Eq = "EQ";
    case And = "AND";
    case Or = "OR";
    case Not = "NOT";
    case Int2Char = "INT2CHAR";
    case Stri2Int = "STRI2INT";
    case Read = "READ";
    case Write = "WRITE";
    case Concat = "CONCAT";
    case StrLen = "STRLEN";
    case GetChar = "GETCHAR";
    case SetChar = "SETCHAR";
    case Type = "TYPE";
    case Label = "LABEL";
    case Jump = "JUMP";
    case JumpIfEq = "JUMPIFEQ";
    case JumpIfNEq = "JUMPIFNEQ";
    case Exit = "EXIT";
    case DPrint = "DPRINT";
    case Break = "BREAK";
}
