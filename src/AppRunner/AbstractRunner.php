<?php

declare(strict_types=1);

namespace App\AppRunner;

use App\Struct\Task;
use Symfony\Component\Process\Process;
use function microtime;

// TODO: separate execution logic implementation in a separate "Executor" and use composition
abstract class AbstractRunner implements AppRunner
{
    protected Process $proc;
    protected Task $task;

    public function stop(): void
    {
        $this->proc->stop(1, 15);
    }

    public function isCompleted(): bool
    {
        if ($this->proc->getStartTime() + $this->task->durationSecs + 2 < microtime(true)) {
            $this->stop();
        }

        return !$this->proc->isRunning();
    }
}
