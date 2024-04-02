<?php

namespace IPP\Student;

class Memory {
    private Frame $globalFrame;
    private Frame|null $temporaryFrame;
    private Frame|null $localFrame;
    /** @var array<int, Frame> */
    private array $localFrames;
    /** @var array<int, Literal> */
    private array $stack;

    public function __construct() {
        $this->globalFrame = new Frame();
        $this->temporaryFrame = null;
        $this->localFrame = null;
        $this->localFrames = [];
    }

    public function getGlobal(string $name): Literal {
        return $this->globalFrame->getVariable($name);
    }

    public function setGlobal(string $name, Literal $value): void {
        $this->globalFrame->setVariable($name, $value);
    }

    public function makeTmp() {
        $this->temporaryFrame = new Frame();
    }

    public function getTmp(string $name): Literal {
        if ($this->temporaryFrame === null) {
            throw new InterpreterException(
                "Cannot get variable "
                    .$name
                    ." from temporary frame. Temporary frame is not set.",
                55
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
                55
            );
        }
        $this->temporaryFrame->setVariable($name, $value);
    }

    public function pushLocal(): void {
        if ($this->temporaryFrame === null) {
            throw new InterpreterException(
                "Cannot push temporary frame to local frames. Temporary frame "
                    ."is not set",
                55
            );
        }
        array_push($this->localFrames, $this->temporaryFrame);
    }

    public function popLocal(): void {
        if (count($this->localFrames) === 0) {
            throw new InterpreterException(
                "Cannot pop local frame. There are no local frames.",
                55
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
                55
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
                55
            );
        }
        end($this->localFrames)->setVariable($name, $value);
    }

    public function popVar(): Literal {
        if (count($this->stack) === 0) {
            throw new InterpreterException(
                "Cannot read variable from stack. The stack is empty."
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
        throw new InterpreterException("Invalid variable frame");
    }
}
