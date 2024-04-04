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

    foreach ($xml->firstElementChild->childNodes as $node) {
        if (!($node instanceof DOMElement)) {
            continue;
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
        if ($inst->opcode == OpCode::Label) {
            $jumpTable[$inst->args[0]] = $idx;
        }
    }

    ksort($insts);
    $instructions = [];
    $idx = 0;
    foreach ($insts as $inst) {
        $instructions[$idx++] = $inst;
    }

    return $instructions;
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
                32
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
            return _read_int($node->nodeValue);
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
            return new Variable(FrameType::Global, $split[1]);
        case "LF":
            return new Variable(FrameType::Local, $split[1]);
        case "TF":
            return new Variable(FrameType::Temporary, $split[1]);
        default:
            throw new InterpreterException(
                "Invalid frame '".$split[0]."'. Expected 'GF', 'LF' or 'TF'.",
                32
            );
    }
}

function _read_nil(string $value): Literal {
    if ($value != "nil") {
        throw new InterpreterException(
            "Invalid nil value. Expected 'nil' but it was '".$value."'.",
            32,
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
            32,
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
                32
            );
    }
}

function _read_string(string $value): Literal {
    if ($value === null) {
        return new Literal("");
    }
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
                32
            );
        }

        $echr = substr($esc, 0, 3);
        $chr = null;
        try {
            $chr = mb_chr((int)$echr);
        } catch (Exception $e) {
            throw new InterpreterException(
                "Invalid string escape '".$esc."'. It is not integer",
                32,
                $e
            );
        }
        if (!$chr) {
            throw new InterpreterException(
                "Invalid string escape '".$esc."'. It is not valid char",
                32
            );
        }
        $res .= $chr;
        $res .= substr($esc, 3);
    }
    return new Literal($res);
}
