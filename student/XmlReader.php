<?php

namespace IPP\Student;

use DOMDocument;
use DOMElement;
use Exception;

/**
 * @param array<string, int> $jumpTable
 * @return array<int, Instruction>
 */
function read_instructions(DOMDocument $xml, array &$jumpTable): array {
    if ($xml->firstElementChild->tagName != "program") {
        throw new InterpreterException(
            "Invalid top level node. Expected 'program' but it was '"
                .$xml->firstChild->nodeName
                ."'.",
            ErrorCode::BadXml
        );
    }

    if ($xml->firstElementChild->getAttribute("language") != "IPPcode24") {
        throw new InterpreterException(
            "Invalid program language. Expected 'IPPcode24' but it was '"
                .$xml->firstElementChild->getAttribute("language")
                ."'.",
            ErrorCode::BadXml
        );
    }

    /** @var array<int, Instruction> */
    $insts = [];

    foreach ($xml->firstElementChild->childNodes as $node) {
        if (!($node instanceof DOMElement)) {
            continue;
        }
        $idx = 0;
        $inst = _read_instruction($node, $idx);
        if (isset($insts[$idx])) {
            throw new InterpreterException(
                "Invalid order, ".$idx." is specified multiple times.",
                ErrorCode::BadXml
            );
        }
        $insts[$idx] = $inst;
    }

    ksort($insts);
    $instructions = [];
    $idx = 0;
    foreach ($insts as $inst) {
        if ($inst->opcode == OpCode::Label) {
            $jumpTable[$inst->args[0]] = $idx;
        }
        $instructions[$idx++] = $inst;
    }

    return $instructions;
}

function _read_instruction(DOMElement $node, int &$order): Instruction {
    if ($node->tagName != "instruction") {
        throw new InterpreterException(
            "Invalid node. Expected 'instruction' but it was '"
                .$node->tagName
                ."'.",
            ErrorCode::BadXml
        );
    }

    $orderS = $node->getAttribute("order");
    if (!$orderS) {
        throw new InterpreterException(
            "Invalid instruction, missing order.",
            ErrorCode::BadXml
        );
    }

    if (!is_numeric($orderS)) {
        throw new InterpreterException(
            "Invalid order. Expected number but it was '"
                .$orderS
                ."',",
            ErrorCode::BadXml
        );
    }
    $order = (int)$orderS - 1;

    if ($order < 0) {
        throw new InterpreterException(
            "Invalid order. Expected positive integer but it was '"
                .$order
                ."',",
            ErrorCode::BadXml
        );
    }

    $opcodeS = $node->getAttribute("opcode");
    if (!$opcodeS) {
        throw new InterpreterException(
            "Invalid instruciton, missing opcode.",
            ErrorCode::BadXml
        );
    }
    $opcode = OpCode::tryFrom(strtoupper($opcodeS));
    if (!$opcode) {
        throw new InterpreterException(
            "Unknown opcode '".$opcodeS."'.",
            ErrorCode::BadXml
        );
    }

    /** @var array<int, Literal|string|Variable> */
    $args = [];
    $max = -1;

    foreach ($node->childNodes as $node) {
        if (!($node instanceof DOMElement)) {
            continue;
        }
        $idx = 0;
        $arg = _read_arg($node, $idx);
        if (isset($args[$idx])) {
            throw new InterpreterException(
                "Invalid arguments. Argument "
                    .$idx
                    ." is specified multiple times",
                ErrorCode::BadXml
            );
        }
        $args[$idx] = $arg;
        if ($idx > $max) {
            $max = $idx;
        }
    }

    if (count($args) != $max + 1) {
        throw new InterpreterException(
            "Invalid arguments. Some argument numbers are skipped.",
            ErrorCode::BadXml
        );
    }

    return new Instruction($opcode, $args);
}

function _read_arg(DOMElement $node, int &$order): Literal|string|Variable {
    switch ($node->tagName) {
        case "arg1":
            $order = 0;
            break;
        case "arg2":
            $order = 1;
            break;
        case "arg3":
            $order = 2;
            break;
        default:
            throw new InterpreterException(
                "Invalid argument node, expected 'arg1', 'arg2' or 'arg3' but"
                    ."it was '"
                    .$node->tagName
                    ."'.",
                ErrorCode::BadXml
            );
    }

    $type = $node->getAttribute("type");
    if (!$type) {
        throw new InterpreterException(
            "Invalid argument. Missing type of the argument.",
            ErrorCode::BadXml
        );
    }

    $value = trim($node->nodeValue);

    switch ($type) {
        case "label":
        case "type":
            return _read_label($value);
        case "var":
            return _read_var($value);
        case "nil":
            return _read_nil($value);
        case "int":
            return _read_int($value);
        case "bool":
            return _read_bool($value);
        case "string":
            return _read_string($value);
        default:
            throw new InterpreterException(
                "Invalid argument type. Expected 'label', 'var', 'nil', 'int',"
                    ."'bool' or 'string', but it was '"
                    .$type
                    ."'.",
                ErrorCode::BadXml,
            );
    }
}

function _read_label(string $value): string {
    if (!$value) {
        throw new InterpreterException(
            "Invalid label. Missing value.",
            ErrorCode::BadXml
        );
    }
    return $value;
}

function _read_var(string $value): Variable {
    $split = explode("@", $value, 2);
    if (count($split) != 2) {
        throw new InterpreterException(
            "Invalid variable '".$value."'. Missing the frame.",
            ErrorCode::BadXml
        );
    }

    if (!$split[1]) {
        throw new InterpreterException(
            "Invalid variable name. It is empty.",
            ErrorCode::BadXml
        );
    }

    switch ($split[0]) {
        case "GF":
            return new Variable(FrameType::Global, $split[1]);
        case "LF":
            return new Variable(FrameType::Local, $split[1]);
        case "TF":
            return new Variable(FrameType::Temporary, $split[1]);
        default:
            throw new InterpreterException(
                "Invalid frame '".$split[0]."'. Expected 'GF', 'LF' or 'TF'.",
                ErrorCode::BadXml
            );
    }
}

function _read_nil(string $value): Literal {
    if ($value != "nil") {
        throw new InterpreterException(
            "Invalid nil value. Expected 'nil' but it was '".$value."'.",
            ErrorCode::BadXml,
        );
    }
    return new Literal(null);
}

function _read_int(string $value): Literal {
    try {
        return new Literal((int)$value);
    } catch (Exception $e) {
        throw new InterpreterException(
            "Invalid int value '".$value."'.",
            ErrorCode::BadXml,
            $e
        );
    }
}

function _read_bool(string $value): Literal {
    switch ($value) {
        case "true":
            return new Literal(true);
        case "false":
            return new Literal(false);
        default:
            throw new InterpreterException(
                "Invalid bool value. Expected 'true' of 'false' but it was '"
                    .$value
                    ."'.",
                ErrorCode::BadXml
            );
    }
}

function _read_string(string $value): Literal {
    $res = "";
    $first = true;
    $escs = explode("\\", $value);
    foreach ($escs as $esc) {
        if ($first) {
            $first = false;
            $res .= $esc;
            continue;
        }

        if (strlen($esc) < 3) {
            throw new InterpreterException(
                "Invalid string escape '".$esc."'. It is too short",
                ErrorCode::BadXml
            );
        }

        $echr = substr($esc, 0, 3);
        $chr = null;
        try {
            $chr = mb_chr((int)$echr);
        } catch (Exception $e) {
            throw new InterpreterException(
                "Invalid string escape '".$esc."'. It is not integer",
                ErrorCode::BadXml,
                $e
            );
        }
        if (!$chr) {
            throw new InterpreterException(
                "Invalid string escape '".$esc."'. It is not valid char",
                ErrorCode::BadXml
            );
        }
        $res .= $chr;
        $res .= substr($esc, 3);
    }
    return new Literal($res);
}
