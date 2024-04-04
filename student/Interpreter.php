<?php

namespace IPP\Student;

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

    public function execute(): int {
        $this->memory = new Memory();
        $this->jumpTable = [];
        $this->nextInst = 1;

        try {
            $this->instructions = read_instructions(
                $this->source->getDOMDocument(),
                $this->jumpTable
            );
        } catch (InterpreterException $ex) {
            $this->stderr->writeString($ex->getMessage());
            return $ex->getCode();
        } catch (IPPException $ex) {
            $this->stderr->writeString($ex->getMessage());
            return $ex->getCode();
        } catch (Exception $ex) {
            $this->stderr->writeString($ex->getMessage());
            return 99;
        }

        // TODO: Start your code here
        // Check \IPP\Core\AbstractInterpreter for predefined I/O objects:
        // $dom = $this->source->getDOMDocument();
        // $val = $this->input->readString();
        // $this->stdout->writeString("stdout");
        // $this->stderr->writeString("stderr");

        throw new NotImplementedException;
    }

    private function run_next(): bool {
        $inst = $this->instructions[$this->nextInst];
        if ($inst === null) {
            // end of code
            return false;
        }
        ++$this->nextInst;

        switch ($inst->opcode) {
            case OpCode::Move:

        }
    }

    private function iMove(Instruction $instruction): bool {
    }

    private function iCreateFrame(Instruction $instruction): bool {
    }

    private function iPushFrame(Instruction $instruction): bool {
    }

    private function iPopFrame(Instruction $instruction): bool {
    }

    private function iDefVar(Instruction $instruction): bool {
    }

    private function iCall(Instruction $instruction): bool {
    }

    private function iReturn(Instruction $instruction): bool {
    }

    private function iPushS(Instruction $instruction): bool {
    }

    private function iPopS(Instruction $instruction): bool {
    }

    private function iAdd(Instruction $instruction): bool {
    }

    private function iSub(Instruction $instruction): bool {
    }

    private function iMul(Instruction $instruction): bool {
    }

    private function iIDiv(Instruction $instruction): bool {
    }

    private function iLt(Instruction $instruction): bool {
    }

    private function iGt(Instruction $instruction): bool {
    }

    private function iEq(Instruction $instruction): bool {
    }

    private function iAnd(Instruction $instruction): bool {
    }

    private function iOr(Instruction $instruction): bool {
    }

    private function iNot(Instruction $instruction): bool {
    }

    private function iInt2Char(Instruction $instruction): bool {
    }

    private function iStr2Int(Instruction $instruction): bool {
    }

    private function iRead(Instruction $instruction): bool {
    }

    private function iWrite(Instruction $instruction): bool {
    }

    private function iConcat(Instruction $instruction): bool {
    }

    private function iStrLen(Instruction $instruction): bool {
    }

    private function iGetChar(Instruction $instruction): bool {
    }

    private function iSetChar(Instruction $instruction): bool {
    }

    private function iType(Instruction $instruction): bool {
    }

    private function iLabel(Instruction $instruction): bool {
    }

    private function iJump(Instruction $instruction): bool {
    }

    private function iJumpIfEq(Instruction $instruction): bool {
    }

    private function iJumpIfNEq(Instruction $instruction): bool {
    }

    private function iExit(Instruction $instruction): bool {
    }

    private function iDPrint(Instruction $instruction): bool {
    }

    private function iBreak(Instruction $instruction): bool {
    }
}
