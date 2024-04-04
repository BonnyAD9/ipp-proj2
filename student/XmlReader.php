<?php

namespace IPP\Student;

use DOMDocument;
use DOMElement;
use Exception;

/** @var array<int, Instruction> */
function read_instructions(
    DOMDocument $xml,
    /** @var array<string, int> */ array &$jumpTable
): array {
    if ($xml->firstElementChild->tagName != "program") {
        throw new InterpreterException(
            "Invalid top level node. Expected 'program' but it was '"
                .$xml->firstChild->nodeName
                ."'.",
            32
        );
    }

    if ($xml->firstElementChild->getAttribute("language") != "IPPcode24") {
        throw new InterpreterException(
            "Invalid program language. Expected 'IPPcode24' but it was '"
                .$xml->firstChild->attributes->getNamedItem("language")->nodeValue
                ."'.",
            32
        );
    }

    /** @var array<int, Instruction> */
    $insts = [];
    $max = 0;

    foreach ($xml->firstElementChild->childNodes as $node) {
        if (!($node instanceof DOMElement)) {
            throw new InterpreterException(
                "Expected element nodes inside the node program",
                32,
            );
        }
        $idx = 0;
        $inst = _read_instruction($node, $idx);
        if (isset($insts[$idx])) {
            throw new InterpreterException(
                "Invalid order, ".$idx." is specified multiple times.",
                32
            );
        }
        $insts[$idx] = $inst;
        if ($idx > $max) {
            $max = $idx;
        }
        if ($inst->opcode == "LABEL" && is_string($inst->args[0])) {
            $jumpTable[$inst->args[0]] = $idx;
        }
    }

    if (count($insts) - 1 != $max) {
        throw new InterpreterException(
            "Invalid opcodes. Some opcodes are missing.",
            32
        );
    }

    return $insts;
}

function _read_instruction(DOMElement $node, int &$order): Instruction {
    if ($node->tagName != "instruction") {
        throw new InterpreterException(
            "Invalid node. Expected 'instruction' but it was '"
                .$node->nodeName
                ."'.",
            32
        );
    }

    $orderS = $node->getAttribute("order");
    if (!$orderS) {
        throw new InterpreterException(
            "Invalid instruction, missing order.",
            32
        );
    }

    try {
        $order = (int)$orderS - 1;
    } catch (Exception $e) {
        throw new InterpreterException(
            "Invalid order. Expected number but it was '"
                .$orderS
                ."',",
            32,
            $e
        );
    }

    if ($order < 0) {
        throw new InterpreterException(
            "Invalid order. Expected positive integer but it was '"
                .$order
                ."',",
            32
        );
    }

    $opcodeS = $node->getAttribute("opcode");
    if (!$opcodeS) {
        throw new InterpreterException(
            "Invalid instruciton, missing opcode.",
            32
        );
    }
    $opcode = OpCode::tryFrom(strtoupper($opcodeS));
    if (!$opcode) {
        throw new InterpreterException("Unknown opcode '".$opcodeS."'.", 32);
    }

    /** @var array<int, Literal|string|Variable> */
    $args = [];
    $max = 0;

    foreach ($node->childNodes as $node) {
        if (!($node instanceof DOMElement)) {
            throw new InterpreterException(
                "Expected element nodes inside of instruction node.",
                32
            );
        }
        $idx = 0;
        $arg = _read_arg($node, $idx);
        if (isset($args[$idx])) {
            throw new InterpreterException(
                "Invalid arguments. Argument "
                    .$idx
                    ." is specified multiple times",
                32
            );
        }
        $args[$idx] = $arg;
        if ($idx > $max) {
            $max = $idx;
        }
    }

    if (count($args) != $max) {
        throw new InterpreterException(
            "Invalid arguments. Some argument numbers are skipped.",
            32
        );
    }

    return new Instruction($opcode, $args);
}

function _read_arg(DOMElement $node, int &$order): Literal|string|Variable {
    switch ($node->nodeName) {
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
                    .$node->nodeName
                    ."'.",
                32
            );
    }

    $type = $node->attributes->getNamedItem("type")->nodeValue;
    if (!$type) {
        throw new InterpreterException(
            "Invalid argument. Missing type of the argument.",
            32
        );
    }

    switch ($type) {
        case "label":
            return _read_label($node->nodeValue);
        case "var":
            return _read_var($node->nodeValue);
        case "nil":
            return _read_nil($node->nodeValue);
        case "int":
            return _read_var($node->nodeValue);
        case "bool":
            return _read_bool($node->nodeValue);
        case "string":
            return _read_string($node->nodeValue);
        default:
            throw new InterpreterException(
                "Invalid argument type. Expected 'label', 'var', 'nil', 'int',"
                    ."'bool' or 'string', but it was '"
                    .$type
                    ."'.",
                32,
            );
    }
}

function _read_label(string $value): string {
    if (!$value) {
        throw new InterpreterException("Invalid label. Missing value.", 32);
    }
    return $value;
}

function _read_var(string $value): Variable {
    $split = explode("@", $value, 2);
    if (count($split) != 2) {
        throw new InterpreterException(
            "Invalid variable '".$value."'. Missing the frame.",
            32
        );
    }

    if (!$split[1]) {
        throw new InterpreterException(
            "Invalid variable name. It is empty.",
            32
        );
    }

    switch ($split[0]) {
        case "GF":
            return new Variable(FrameType::Global, $split[2]);
        case "LF":
            return new Variable(FrameType::Local, $split[2]);
        case "TF":
            return new Variable(FrameType::Temporary, $split[2]);
        default:
            throw new InterpreterException(
                "Invalid frame '".$split[0]."'. Expected 'GF', 'LF' or 'TF'.",
                32
            );
    }
}

function _read_nil(string $value): null {
    if ($value != "nil") {
        throw new InterpreterException(
            "Invalid nil value. Expected 'nil' but it was '".$value."'.",
            32,
        );
    }
    return null;
}

function _read_int(string $value): int {
    try {
        return (int)$value;
    } catch (Exception $e) {
        throw new InterpreterException(
            "Invalid int value '".$value."'.",
            32,
            $e
        );
    }
}

function _read_bool(string $value): bool {
    switch ($value) {
        case "true":
            return true;
        case "false":
            return false;
        default:
            throw new InterpreterException(
                "Invalid bool value. Expected 'true' of 'false' but it was '"
                    .$value
                    ."'.",
                32
            );
    }
}

function _read_string(string $value): string {
    return $value ?? "";
}
