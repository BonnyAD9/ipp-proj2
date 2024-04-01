<?php

namespace IPP\Student;

use IPP\Core\AbstractInterpreter;
use IPP\Core\Exception\NotImplementedException;

class Interpreter extends AbstractInterpreter
{
    private Frame $globalFrame;
    private Frame|null $temporaryFrame;
    private Frame|null $localFrame;
    /**
     * @var array<int, Frame>
     */
    private array $localFrames;

    public function execute(): int {
        $this->globalFrame = new Frame();
        $this->temporaryFrame = null;
        $this->localFrame = null;
        $this->localFrames = [];

        // TODO: Start your code here
        // Check \IPP\Core\AbstractInterpreter for predefined I/O objects:
        // $dom = $this->source->getDOMDocument();
        // $val = $this->input->readString();
        // $this->stdout->writeString("stdout");
        // $this->stderr->writeString("stderr");

        throw new NotImplementedException;
    }
}
