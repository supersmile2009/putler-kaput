<?php

declare(strict_types=1);

namespace App\AppRunner;

use Symfony\Component\Process\Process;
use function microtime;

// TODO: separate execution logic implementation in a separate "Executor" and use composition
abstract class AbstractRunner implements AppRunner
{
    public const DURATION_SEC = 60;
    protected Process $proc;

    public function stop(): void
    {
        $this->proc->stop(1, 15);
    }

    public function isCompleted(): bool
    {
        if ($this->proc->getStartTime() + self::DURATION_SEC + 2 < microtime(true)) {
            $this->stop();
        }

        return !$this->proc->isRunning();
    }
}
