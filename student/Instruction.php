<?php

namespace IPP\Student;

class Instruction {
    public OpCode $opcode;
    /** @var array<int, Literal|string|Variable> */
    public array $args;

    public function __construct(
        OpCode $opcode,
        /** @var array<int, Literal|string|Variable> */ array $args
    ) {
        $this->opcode = $opcode;
        $this->args = $args;

        switch ($this->opcode) {
            case OpCode::Move:
                $this->validateMove();
                break;
            case OpCode::CreateFrame:
                $this->validateCreateFrame();
                break;
            case OpCode::PushFrame:
                $this->validatePushFrame();
                break;
            case OpCode::PopFrame:
                $this->validatePopFrame();
                break;
            case OpCode::DefVar:
                $this->validateDefVar();
                break;
            case OpCode::Call:
                $this->validateCall();
                break;
            case OpCode::Return:
                $this->validateReturn();
                break;
            case OpCode::PushS:
                $this->validatePushS();
                break;
            case OpCode::PopS:
                $this->validatePopS();
                break;
            case OpCode::Add:
                $this->validateAdd();
                break;
            case OpCode::Sub:
                $this->validateSub();
                break;
            case OpCode::Mul:
                $this->validateMul();
                break;
            case OpCode::IDiv:
                $this->validateIDiv();
                break;
            case OpCode::Lt:
                $this->validateLt();
                break;
            case OpCode::Gt:
                $this->validateGt();
                break;
            case OpCode::Eq:
                $this->validateEq();
                break;
            case OpCode::And:
                $this->validateAnd();
                break;
            case OpCode::Or:
                $this->validateOr();
                break;
            case OpCode::Not:
                $this->validateNot();
                break;
            case OpCode::Int2Char:
                $this->validateInt2Char();
                break;
            case OpCode::Stri2Int:
                $this->validateStri2Int();
                break;
            case OpCode::Read:
                $this->validateRead();
                break;
            case OpCode::Write:
                $this->validateWrite();
                break;
            case OpCode::Concat:
                $this->validateConcat();
                break;
            case OpCode::StrLen:
                $this->validateStrLen();
                break;
            case OpCode::GetChar:
                $this->validateGetChar();
                break;
            case OpCode::SetChar:
                $this->validateSetChar();
                break;
            case OpCode::Type:
                $this->validateType();
                break;
            case OpCode::Label:
                $this->validateLabel();
                break;
            case OpCode::Jump:
                $this->validateJump();
                break;
            case OpCode::JumpIfEq:
                $this->validateJumpIfEq();
                break;
            case OpCode::JumpIfNEq:
                $this->validateJumpIfNeq();
                break;
            case OpCode::Exit:
                $this->validateExit();
                break;
            case OpCode::DPrint:
                $this->validateDPrint();
                break;
            case OpCode::Break:
                $this->validateBreak();
                break;
            default:
                throw new InterpreterException("Invalid op code", 53);
        }
    }

    public function validateMove() {
        $this->checkArgCnt(2);
        $this->checkVar(0);
        $this->checkSymbAny(1);
    }

    public function validateCreateFrame() {
        $this->checkArgCnt(0);
    }

    public function validatePushFrame() {
        $this->checkArgCnt(0);
    }

    public function validatePopFrame() {
        $this->checkArgCnt(0);
    }

    public function validateDefVar() {
        $this->checkArgCnt(1);
        $this->checkVar(0);
    }

    public function validateCall() {
        $this->checkArgCnt(1);
        $this->checkLabel(0);
    }

    public function validateReturn() {
        $this->checkArgCnt(0);
    }

    public function validatePushS() {
        $this->checkArgCnt(1);
        $this->checkSymbAny(0);
    }

    public function validatePopS() {
        $this->checkArgCnt(1);
        $this->checkVar(0);
    }

    public function validateAdd() {
        $this->checkArgCnt(3);
        $this->checkVar(0);
        $this->checkSymb(1, VarType::Int);
        $this->checkSymb(2, VarType::Int);
    }

    public function validateSub() {
        $this->validateAdd();
    }

    public function validateMul() {
        $this->validateAdd();
    }

    public function validateIDiv() {
        $this->validateAdd();
    }

    public function validateLt() {
        $this->checkArgCnt(3);
        $this->checkVar(0);
        $this->checkSymbAny(1);
        $this->checkNotType(1, VarType::Nil);
        $this->checkSymbAny(2);
        $this->checkNotType(2, VarType::Nil);
        $this->checkSame(1, 2);
    }

    public function validateGt() {
        $this->validateLt();
    }

    public function validateEq() {
        $this->validateLt();
    }

    public function validateAnd() {
        $this->checkArgCnt(3);
        $this->checkVar(0);
        $this->checkSymb(1, VarType::Bool);
        $this->checkSymb(2, VarType::Bool);
    }

    public function validateOr() {
        $this->validateAnd();
    }

    public function validateNot() {
        $this->checkArgCnt(3);
        $this->checkVar(0);
        $this->checkSymb(1, VarType::Bool);
    }

    public function validateInt2Char() {
        $this->checkArgCnt(2);
        $this->checkVar(0);
        $this->checkSymb(1, VarType::Int);
    }

    public function validateStri2Int() {
        $this->checkArgCnt(3);
        $this->checkVar(0);
        $this->checkSymb(1, VarType::String);
        $this->checkSymb(2, VarType::Int);
    }

    public function validateRead() {
        $this->checkArgCnt(2);
        $this->checkVar(0);
        $this->checkLabel(1);
        if ($this->args[1] != "int"
            && $this->args[1] != "string"
            && $this->args[1] != "bool"
        ) {
            throw new InterpreterException(
                "Invalid argument 1 to instruction READ. Argument must be "
                    ."'int', 'string' or 'bool'.",
                53
            );
        }
    }

    public function validateWrite() {
        $this->checkArgCnt(1);
        $this->checkSymbAny(0);
    }

    public function validateConcat() {
        $this->checkArgCnt(3);
        $this->checkVar(0);
        $this->checkSymb(1, VarType::String);
        $this->checkSymb(2, VarType::String);
    }

    public function validateStrLen() {
        $this->checkArgCnt(2);
        $this->checkVar(0);
        $this->checkSymb(1, VarType::String);
    }

    public function validateGetChar() {
        $this->checkArgCnt(3);
        $this->checkVar(0);
        $this->checkSymb(1, VarType::String);
        $this->checkSymb(2, VarType::Int);
    }

    public function validateSetChar() {
        $this->validateGetChar();
    }

    public function validateType() {
        $this->checkArgCnt(2);
        $this->checkVar(0);
        $this->checkSymbAny(1);
    }

    public function validateLabel() {
        $this->checkArgCnt(1);
        $this->checkLabel(0);
    }

    public function validateJump() {
        $this->checkArgCnt(1);
        $this->checkLabel(0);
    }

    public function validateJumpIfEq() {
        $this->checkArgCnt(3);
        $this->checkLabel(0);
        $this->checkSymbAny(1);
        $this->checkSymbAny(2);
    }

    public function validateJumpIfNEq() {
        $this->validateJumpIfEq();
    }

    public function validateExit() {
        $this->checkArgCnt(1);
        $this->checkSymb(0, VarType::Int);
    }

    public function validateDPrint() {
        $this->checkArgCnt(1);
        $this->checkSymbAny(0);
    }

    public function validateBreak() {
        $this->checkArgCnt(0);
    }

    public function checkVar(int $arg) {
        if (!($this->args[$arg] instanceof Variable)) {
            throw new InterpreterException(
                "Invalid argument type for argument "
                    .$arg
                    ." of instruction "
                    .$this->opcode->value
                    .". Expected variable.",
                53
            );
        }
    }

    public function checkSymbAny(int $arg) {
        $a = $this->args[$arg];
        if (!($a instanceof Variable) && !($a instanceof Literal)) {
            throw new InterpreterException(
                "Invalid argument type for argument "
                    .$arg
                    ." of instruction "
                    .$this->opcode->value
                    .". Expected variable or literal.",
                53
            );
        }
    }

    public function checkSymb(int $arg, VarType $type) {
        $a = $this->args[$arg];
        if ($a instanceof Variable) {
            return;
        }
        if ($a instanceof Literal) {
            if ($a->type === $type) {
                return;
            }
        }
        throw new InterpreterException(
            "Invalid argument type for argument "
                .$arg
                ." of instruction "
                .$this->opcode->value
                .". Expected variable or literal of type "
                .$type->name
                .".",
            53
        );
    }

    public function checkLabel(int $arg) {
        if (!is_string($this->args[$arg])) {
            throw new InterpreterException(
                "Invalid argument type for argument "
                    .$arg
                    ." of instruction "
                    .$this->opcode->value
                    .". Expected label.",
                53
            );
        }
    }

    public function checkSame(int $arg1, int $arg2) {
        $a1 = $this->args[$arg1];
        $a2 = $this->args[$arg2];
        if ($a1 instanceof Literal && $a2 instanceof Literal) {
            if ($a1->type != $a2->type) {
                throw new InterpreterException(
                    "Invalid argument types to instruction "
                        .$this->opcode->value
                        ."Arguments "
                        .$arg1
                        ." and "
                        .$arg2
                        ." must have the same type",
                    53
                );
            }
        }
    }

    public function checkNotType(int $arg, VarType $type) {
        $a = $this->args[$arg];
        if ($a instanceof Literal && $a->type == $type) {
            throw new InterpreterException(
                "Invalid argument type to instruction "
                    .$this->opcode->value
                    ."Argument "
                    .$arg
                    ." may not be of type "
                    .$type->name
                    .".",
                53
            );
        }
    }

    public function checkArgCnt(int $cnt) {
        if (count($this->args) != $cnt) {
            throw new InterpreterException(
                "Invalid number of arguments for instruciton "
                    .$this->opcode->value
                    .". Expected "
                    .$cnt
                    ." but have "
                    .count($this->args)
                    .".",
                    53
            );
        }
    }
}
