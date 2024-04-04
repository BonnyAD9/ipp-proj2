<?php

namespace IPP\Student;

class Memory {
    private Frame $globalFrame;
    private Frame|null $temporaryFrame;
    /** @var array<int, Frame> */
    private array $localFrames;
    /** @var array<int, Literal> */
    private array $stack;

    public function __construct() {
        $this->globalFrame = new Frame();
        $this->temporaryFrame = null;
        $this->localFrames = [];
        $this->stack = [];
    }

    public function getGlobal(string $name): Literal {
        return $this->globalFrame->getVariable($name);
    }

    public function setGlobal(string $name, Literal $value): void {
        $this->globalFrame->setVariable($name, $value);
    }

    public function declGlobal(string $name): void {
        $this->globalFrame->declVariable($name);
    }

    public function isDeclGlobal(string $name): bool {
        return $this->globalFrame->isDecl($name);
    }

    public function makeTmp(): void {
        $this->temporaryFrame = new Frame();
    }

    public function getTmp(string $name): Literal {
        if ($this->temporaryFrame === null) {
            throw new InterpreterException(
                "Cannot get variable "
                    .$name
                    ." from temporary frame. Temporary frame is not set.",
                ErrorCode::NoFrame
            );
        }
        return $this->temporaryFrame->getVariable($name);
    }

    public function setTmp(string $name, Literal $value): void {
        if ($this->temporaryFrame === null) {
            throw new InterpreterException(
                "Cannot set variable "
                    .$name
                    ." from temporary frame. Temporary frame is not set.",
                ErrorCode::NoFrame
            );
        }
        $this->temporaryFrame->setVariable($name, $value);
    }

    public function declTmp(string $name): void {
        if ($this->temporaryFrame === null) {
            throw new InterpreterException(
                "Cannot declare variable "
                    .$name
                    ." in temporary frame. Temporary frame is not set.",
                ErrorCode::NoFrame
            );
        }
        $this->temporaryFrame->declVariable($name);
    }

    public function isDeclTmp(string $name): bool {
        return $this->temporaryFrame !== null
            && $this->temporaryFrame->isDecl($name);
    }

    public function pushLocal(): void {
        if ($this->temporaryFrame === null) {
            throw new InterpreterException(
                "Cannot push temporary frame to local frames. Temporary frame "
                    ."is not set",
                ErrorCode::NoFrame
            );
        }
        array_push($this->localFrames, $this->temporaryFrame);
    }

    public function popLocal(): void {
        if (count($this->localFrames) === 0) {
            throw new InterpreterException(
                "Cannot pop local frame. There are no local frames.",
                ErrorCode::NoFrame
            );
        }
        $this->temporaryFrame = array_pop($this->localFrames);
    }

    public function getLocal(string $name): Literal {
        if (count($this->localFrames) === 0) {
            throw new InterpreterException(
                "Cannot read variable "
                    .$name
                    ." from local frame. There is no local frame.",
                ErrorCode::NoFrame
            );
        }
        return end($this->localFrames)->getVariable($name);
    }

    public function setLocal(string $name, Literal $value): void {
        if (count($this->localFrames) === 0) {
            throw new InterpreterException(
                "Cannot set variable "
                    .$name
                    ." from local frame. There is no local frame",
                ErrorCode::NoFrame
            );
        }
        end($this->localFrames)->setVariable($name, $value);
    }

    public function declLocal(string $name): void {
        if (count($this->localFrames) === 0) {
            throw new InterpreterException(
                "Cannot declare variable "
                    .$name
                    ." in local frame. There is no local frame.",
                ErrorCode::NoFrame
            );
        }
        end($this->localFrames)->declVariable($name);
    }

    public function isDeclLocal(string $name): bool {
        return count($this->localFrames) !== 0
            && end($this->localFrames)->isDecl($name);
    }

    public function popVar(): Literal {
        if (count($this->stack) === 0) {
            throw new InterpreterException(
                "Cannot read variable from stack. The stack is empty.",
                ErrorCode::NoValue
            );
        }
        return array_pop($this->stack);
    }

    public function pushVar(Literal $value): void {
        array_push($this->stack, $value);
    }

    public function getVar(Variable $name): Literal {
        switch ($name->frame) {
            case FrameType::Global:
                return $this->getGlobal($name->name);
            case FrameType::Local:
                return $this->getLocal($name->name);
            case FrameType::Temporary:
                return $this->getTmp($name->name);
        }
    }

    public function setVar(Variable $name, Literal $value): void {
        switch ($name->frame) {
            case FrameType::Global:
                $this->setGlobal($name->name, $value);
                return;
            case FrameType::Local:
                $this->setLocal($name->name, $value);
                return;
            case FrameType::Temporary:
                $this->setTmp($name->name, $value);
                return;
        }
    }

    public function declVar(Variable $name): void {
        switch ($name->frame) {
            case FrameType::Global:
                $this->declGlobal($name->name);
                return;
            case FrameType::Local:
                $this->declLocal($name->name);
                return;
            case FrameType::Temporary:
                $this->declTmp($name->name);
                return;
        }
    }

    public function isDeclVar(Variable $name): bool {
        switch ($name->frame) {
            case FrameType::Global:
                return $this->isDeclGlobal($name->name);
            case FrameType::Local:
                return $this->isDeclLocal($name->name);
            case FrameType::Temporary:
                return $this->isDeclTmp($name->name);
        }
    }
}
