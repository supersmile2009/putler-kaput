<?php

declare(strict_types=1);

namespace App\AppRunner;

use App\Struct\Task;
use Symfony\Component\Process\Process;
use function fopen;
use function fwrite;

class BombardierRunner extends AbstractRunner
{
    private array $args = ['-c', '1000', '-t', '2s', '-p', 'i,r', '--http1', '--insecure'];

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function run(): void
    {
        $args = $this->buildCommandArgs();
        $this->proc = new Process(command: $args, timeout: 0.0);
        $this->proc->start(function ($type, $data) {
            if ($type === Process::ERR) {
                fwrite(fopen('php://stderr', 'wb+'), $data);
            } else {
                fwrite(fopen('php://stdout', 'wb+'), $data);
            }
        });
    }

    private function buildCommandArgs(): array
    {
        $url = $this->task->host;
        if ($this->task->port !== 0) {
            $url .= ':'.$this->task->port;
        }

        return ['/bin/bombardier', '-d', $this->task->durationSecs.'s', ...$this->args, $url];
    }
}
