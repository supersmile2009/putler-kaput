<?php

declare(strict_types=1);

namespace App\AppRunner;

use App\Struct\Task;
use Symfony\Component\Process\Process;
use function fopen;
use function fwrite;
use function microtime;

class DRipperRunner extends AbstractRunner
{
    private bool $stopRequested = false;

    public function __construct(Task $task)
    {
        $this->task = $task;
        $args = [
            '/usr/bin/python3',
            '-u',
            '/opt/dripper/DRipper.py',
            '-s',
            $this->task->host,
            '-p',
            $this->task->port,
            '-t',
            '150',
            ...$this->task->commandArgs,
        ];
        $this->proc = new Process(command: $args, timeout: 0.0);
    }
    public function run(): void
    {
        $this->proc->start(function ($type, $data) {
            if ($type === Process::ERR) {
                fwrite(fopen('php://stderr', 'wb+'), $data);
            } else {
                fwrite(fopen('php://stdout', 'wb+'), $data);
            }
        });
    }

    public function isCompleted(): bool
    {
        // TODO: use Swoole timer
        if (!$this->stopRequested && $this->proc->getStartTime() + $this->task->durationSecs < microtime(true)) {
            $this->stopRequested = true;
            if ($this->proc->isRunning()) {
                $this->proc->signal(15);
            }
        }

        return parent::isCompleted();
    }
}
