<?php

namespace IPP\Student;

require_once("XmlReader.php");

use IPP\Core\AbstractInterpreter;
use IPP\Core\Exception\IPPException;
use IPP\Core\Exception\NotImplementedException;
use Exception;

class Interpreter extends AbstractInterpreter {
    private Memory $memory;
    /** @var array<int, Instruction> */
    private array $instructions;
    /** @var array<string, int> */
    private array $jumpTable;
    private int $nextInst;
    /** @var array<int, int> */
    private array $callStack;
    private int $exitCode;

    public function execute(): int {
        $this->memory = new Memory();
        $this->jumpTable = [];
        $this->nextInst = 0;
        $this->callStack = [];
        $this->exitCode = 0;

        $this->instructions = read_instructions(
            $this->source->getDOMDocument(),
            $this->jumpTable
        );

        while ($this->runNext()) {}

        return $this->exitCode;
    }

    private function runNext(): bool {
        if (!isset($this->instructions[$this->nextInst])) {
            // end of code
            return false;
        }
        $inst = $this->instructions[$this->nextInst];
        ++$this->nextInst;

        switch ($inst->opcode) {
            case OpCode::Move:
                return $this->iMove($inst);
            case OpCode::CreateFrame:
                return $this->iCreateFrame($inst);
            case OpCode::PushFrame:
                return $this->iPushFrame($inst);
            case OpCode::PopFrame:
                return $this->iPopFrame($inst);
            case OpCode::DefVar:
                return $this->iDefVar($inst);
            case OpCode::Call:
                return $this->iCall($inst);
            case OpCode::Return:
                return $this->iReturn($inst);
            case OpCode::PushS:
                return $this->iPushS($inst);
            case OpCode::PopS:
                return $this->iPopS($inst);
            case OpCode::Add:
                return $this->iAdd($inst);
            case OpCode::Sub:
                return $this->iSub($inst);
            case OpCode::Mul:
                return $this->iMul($inst);
            case OpCode::IDiv:
                return $this->iIDiv($inst);
            case OpCode::Lt:
                return $this->iLt($inst);
            case OpCode::Gt:
                return $this->iGt($inst);
            case OpCode::Eq:
                return $this->iEq($inst);
            case OpCode::And:
                return $this->iAnd($inst);
            case OpCode::Or:
                return $this->iOr($inst);
            case OpCode::Not:
                return $this->iNot($inst);
            case OpCode::Int2Char:
                return $this->iInt2Char($inst);
            case OpCode::Stri2Int:
                return $this->iStri2Int($inst);
            case OpCode::Read:
                return $this->iRead($inst);
            case OpCode::Write:
                return $this->iWrite($inst);
            case OpCode::Concat:
                return $this->iConcat($inst);
            case OpCode::StrLen:
                return $this->iStrLen($inst);
            case OpCode::GetChar:
                return $this->iGetChar($inst);
            case OpCode::SetChar:
                return $this->iSetChar($inst);
            case OpCode::Type:
                return $this->iType($inst);
            case OpCode::Label:
                return $this->iLabel($inst);
            case OpCode::Jump:
                return $this->iJump($inst);
            case OpCode::JumpIfEq:
                return $this->iJumpIfEq($inst);
            case OpCode::JumpIfNEq:
                return $this->iJumpIfNEq($inst);
            case OpCode::Exit:
                return $this->iExit($inst);
            case OpCode::DPrint:
                return $this->iDPrint($inst);
            case OpCode::Break:
                return $this->iBreak($inst);
            default:
                throw new InterpreterException("Invalid opcode.", 52);
        }
    }

    private function iMove(Instruction $inst): bool {
        $this->memory->setVar($inst->args[0], $this->getValue($inst->args[1]));
        return true;
    }

    private function iCreateFrame(Instruction $inst): bool {
        $this->memory->makeTmp();
        return true;
    }

    private function iPushFrame(Instruction $inst): bool {
        $this->memory->pushLocal();
        return true;
    }

    private function iPopFrame(Instruction $inst): bool {
        $this->memory->popLocal();
        return true;
    }

    private function iDefVar(Instruction $inst): bool {
        $this->memory->declVar($inst->args[0]);
        return true;
    }

    private function iCall(Instruction $inst): bool {
        array_push($this->callStack, $this->nextInst);
        $this->jumpTo($inst->args[0]);
        return true;
    }

    private function iReturn(Instruction $inst): bool {
        if (count($this->callStack) === 0) {
            throw new InterpreterException(
                "Cannot return. There are no values in the call stack",
                52
            );
        }
        $this->nextInst = array_pop($this->callStack);
        return true;
    }

    private function iPushS(Instruction $inst): bool {
        $this->memory->pushVar($this->getValue($inst->args[0]));
        return true;
    }

    private function iPopS(Instruction $inst): bool {
        $this->memory->setVar($inst->args[0], $this->memory->popVar());
        return true;
    }

    private function iAdd(Instruction $inst): bool {
        $a = $this->getValueType($inst->args[1], VarType::Int);
        $b = $this->getValueType($inst->args[2], VarType::Int);
        $this->memory->setVar($inst->args[0], new Literal($a + $b));
        return true;
    }

    private function iSub(Instruction $inst): bool {
        $a = $this->getValueType($inst->args[1], VarType::Int);
        $b = $this->getValueType($inst->args[2], VarType::Int);
        $this->memory->setVar($inst->args[0], new Literal($a - $b));
        return true;
    }

    private function iMul(Instruction $inst): bool {
        $a = $this->getValueType($inst->args[1], VarType::Int);
        $b = $this->getValueType($inst->args[2], VarType::Int);
        $this->memory->setVar($inst->args[0], new Literal($a * $b));
        return true;
    }

    private function iIDiv(Instruction $inst): bool {
        $a = $this->getValueType($inst->args[1], VarType::Int);
        $b = $this->getValueType($inst->args[2], VarType::Int);
        $this->memory->setVar($inst->args[0], new Literal(intdiv($a, $b)));
        return true;
    }

    private function iLt(Instruction $inst): bool {
        $al = $this->getValue($inst->args[1]);
        $b = $this->getValueType($inst->args[2], $al->type);
        $a = $al->value;
        $v = $inst->args[0];

        switch ($al->type) {
            case VarType::Int:
                $this->memory->setVar($v, new Literal($a < $b));
                break;
            case VarType::Bool:
                $this->memory->setVar($v, new Literal(!$a && $b));
                break;
            case VarType::String:
                $this->memory->setVar($v, new Literal(strcmp($a, $b) < 0));
                break;
            default:
                throw new InterpreterException(
                    "Cannot compare values of type ".$al->type.".",
                    53
                );
        }
        return true;
    }

    private function iGt(Instruction $inst): bool {
        $al = $this->getValue($inst->args[1]);
        $b = $this->getValueType($inst->args[2], $al->type);
        $a = $al->value;
        $v = $inst->args[0];

        switch ($al->type) {
            case VarType::Int:
                $this->memory->setVar($v, new Literal($a > $b));
                break;
            case VarType::Bool:
                $this->memory->setVar($v, new Literal($a && !$b));
                break;
            case VarType::String:
                $this->memory->setVar($v, new Literal(strcmp($a, $b) > 0));
                break;
            default:
                throw new InterpreterException(
                    "Cannot compare values of type ".$al->type.".",
                    53
                );
        }
        return true;
    }

    private function iEq(Instruction $inst): bool {
        $al = $this->getValue($inst->args[1]);
        $b = $this->getValueType($inst->args[2], $al->type);
        $a = $al->value;
        $v = $inst->args[0];

        $this->memory->setVar($v, new Literal($a == $b));
        return true;
    }

    private function iAnd(Instruction $inst): bool {
        $v = $inst->args[0];
        $a = $this->getValueType($inst->args[1], VarType::Bool);
        $b = $this->getValueType($inst->args[2], VarType::Bool);
        $this->memory->setVar($v, new Literal($a && $b));
        return true;
    }

    private function iOr(Instruction $inst): bool {
        $v = $inst->args[0];
        $a = $this->getValueType($inst->args[1], VarType::Bool);
        $b = $this->getValueType($inst->args[2], VarType::Bool);
        $this->memory->setVar($v, new Literal($a || $b));
        return true;
    }

    private function iNot(Instruction $inst): bool {
        $v = $inst->args[0];
        $a = $this->getValueType($inst->args[1], VarType::Bool);
        $this->memory->setVar($v, new Literal(!$a));
        return true;
    }

    private function iInt2Char(Instruction $inst): bool {
        $v = $inst->args[0];
        $a = $this->getValueType($inst->args[1], VarType::Int);
        $r = mb_chr($a);
        if (!$r) {
            throw new InterpreterException(
                "Invalid unicode code point ".$a.".",
                57
            );
        }
        $this->memory->setVar($v, new Literal($r));
        return true;
    }

    private function iStri2Int(Instruction $inst): bool {
        $v = $inst->args[0];
        $s = $this->getValueType($inst->args[1], VarType::String);
        $i = $this->getValueType($inst->args[2], VarType::Int);
        $len = strlen($s);
        if ($i >= $len) {
            throw new InterpreterException(
                "Cannot get value of string at index "
                    .$i
                    ." because the string has length "
                    .$len
                    .".",
                58
            );
        }
        $r = mb_ord($s[$i]);
        $this->memory->setVar($v, new Literal($r));
        return true;
    }

    private function iRead(Instruction $inst): bool {
        $v = $inst->args[0];
        $r = null;
        switch ($inst->args[1]) {
            case "int":
                $r = $this->input->readInt();
                break;
            case "string":
                $r = $this->input->readString();
                break;
            case "bool":
                $r = $this->input->readBool();
                break;
            default:
                throw new InterpreterException(
                    "Invalid type ".$inst->args[1]." for instruction read",
                    53
                );
        }
        $this->memory->setVar($v, new Literal($r));
        return true;
    }

    private function iWrite(Instruction $inst): bool {
        $s = $this->getValue($inst->args[0]);
        switch ($s->type) {
            case VarType::Nil:
                break;
            case VarType::Int:
                $this->stdout->writeInt($s->value);
                break;
            case VarType::Bool:
                $this->stdout->writeBool($s->value);
                break;
            case VarType::String:
                $this->stdout->writeString($s->value);
                break;
        }
        return true;
    }

    private function iConcat(Instruction $inst): bool {
        $v = $inst->args[0];
        $a = $this->getValueType($inst->args[1], VarType::String);
        $b = $this->getValueType($inst->args[2], VarType::String);
        $this->memory->setVar($v, new Literal($a.$b));
        return true;
    }

    private function iStrLen(Instruction $inst): bool {
        $v = $inst->args[0];
        $s = $this->getValueType($inst->args[1], VarType::String);
        $this->memory->setVar($v, new Literal(strlen($s)));
        return true;
    }

    private function iGetChar(Instruction $inst): bool {
        $v = $inst->args[0];
        $s = $this->getValueType($inst->args[1], VarType::String);
        $i = $this->getValueType($inst->args[2], VarType::Int);
        $this->memory->setVar($v, new Literal($s[$i]));
        return true;
    }

    private function iSetChar(Instruction $inst): bool {
        /** @var string */
        $s = $this->getValueType($inst->args[0], VarType::String);
        $v = $this->getValueType($inst->args[1], VarType::String);
        $i = $this->getValueType($inst->args[2], VarType::Int);
        $s[$i] = $v;
        $this->memory->setVar($inst->args[0], new Literal($s));
        return true;
    }

    private function iType(Instruction $inst): bool {
        $v = $inst->args[0];
        $type = null;
        if ($inst->args[1] instanceof Literal) {
            $type = $inst->args[1]->type;
        } else if ($this->memory->isDeclVar($inst->args[1])) {
            $type = $this->memory->getVar($inst->args[1])->type;
        }
        switch ($type) {
            case null:
                $this->memory->setVar($v, new Literal(""));
                break;
            case VarType::Nil:
                $this->memory->setVar($v, new Literal("nil"));
                break;
            case VarType::Int:
                $this->memory->setVar($v, new Literal("int"));
                break;
            case VarType::String:
                $this->memory->setVar($v, new Literal("string"));
                break;
            case VarType::Bool:
                $this->memory->setVar($v, new Literal("bool"));
                break;
        }
        return true;
    }

    private function iLabel(Instruction $inst): bool {
        return true;
    }

    private function iJump(Instruction $inst): bool {
        $this->jumpTo($inst->args[0]);
        return true;
    }

    private function iJumpIfEq(Instruction $inst): bool {
        $al = $this->getValue($inst->args[1]);
        $b = $this->getValueType($inst->args[2], $al->type);
        $a = $al->value;

        if ($a == $b) {
            $this->jumpTo($inst->args[0]);
        }
        return true;
    }

    private function iJumpIfNEq(Instruction $inst): bool {
        $al = $this->getValue($inst->args[1]);
        $b = $this->getValueType($inst->args[2], $al->type);
        $a = $al->value;

        if ($a != $b) {
            $this->jumpTo($inst->args[0]);
        }
        return true;
    }

    private function iExit(Instruction $inst): bool {
        $c = $this->getValueType($inst->args[0], VarType::Int);
        if ($c < 0 || $c > 9) {
            throw new InterpreterException(
                "Invalid exit code "
                    .$c
                    .". It must be between 0 and 9 (inclusive)",
                57
            );
        }
        $this->exitCode = $c;
        return false;
    }

    private function iDPrint(Instruction $inst): bool {
        $s = $this->getValue($inst->args[0]);
        switch ($s->type) {
            case VarType::Nil:
                break;
            case VarType::Int:
                $this->stderr->writeInt($s->value);
                break;
            case VarType::Bool:
                $this->stderr->writeBool($s->value);
                break;
            case VarType::String:
                $this->stderr->writeString($s->value);
                break;
        }
        return true;
    }

    private function iBreak(Instruction $inst): bool {
        $this->stderr->writeString("Code position: ".$this->nextInst."\n");
        return true;
    }

    private function jumpTo(string $label) {
        $pos = $this->jumpTable[$label];
        if ($pos === null) {
            throw new InterpreterException(
                "Cannot jump to label ".$label.". There is no such label.",
                52
            );
        }
        $this->nextInst = $pos;
    }

    private function getValueType(Literal|Variable $var, VarType $type): null|int|string|bool {
        if ($var->type !== $type) {
            throw new InterpreterException(
                "Invalid operand type. Expected "
                    .$type
                    ." but have "
                    .$var->type
                    .".",
                53
            );
        }
        return $var->value;
    }

    private function  getValue(Literal|Variable $var): Literal {
        if ($var instanceof Literal) {
            return $var;
        }
        return $this->memory->getVar($var);
    }
}
