<?php

declare(strict_types=1);

namespace App\AppRunner;

use App\Struct\Task;
use Symfony\Component\Process\Process;
use function fopen;
use function fwrite;

class DNSPerfRunner extends AbstractRunner
{
    private array $args = ['-c', '1', '-S', '1', '-Q', '10000', '-l', '60', '-d', '/opt/queryfile'];
    private Task $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function run(): void
    {
        $args = ['/bin/dnsperf', '-s', $this->task->host, '-p', $this->task->port, ...$this->args];
        $this->proc = new Process(command: $args, timeout: 0.0);
        $this->proc->start(function ($type, $data) {
            if ($type === Process::ERR) {
                fwrite(fopen('php://stderr', 'wb+'), $data);
            } else {
                fwrite(fopen('php://stdout', 'wb+'), $data);
            }
        });
    }
}
