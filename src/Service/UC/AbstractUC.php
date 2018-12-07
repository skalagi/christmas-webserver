<?php

namespace Syntax\Service\UC;

use Symfony\Component\Process\Process;
use Syntax\Exception\AVRException;

abstract class AbstractUC
{
    /**
     * @param $command
     * @throws AVRException
     */
    protected function _execute($command)
    {
        $process = new Process($command);
        $process->run();

        if(!$process->isSuccessful()) {
            throw new AVRException($process->getErrorOutput());
        }
    }
}