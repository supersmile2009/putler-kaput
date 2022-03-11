<?php

namespace App\Struct;

use InvalidArgumentException;
use function implode;

/**
 * @immutable
 */
class Task
{
    public string $id;
    public TaskDriver $driver;
    public TaskApp $app;
    /**
     * @var list<string>
     */
    public array $commandArgs;
    public string $host;
    public int $durationSecs;
    public int $port;

    /**
     * @param list<string> $commandArgs
     */
    public function __construct(TaskDriver $driver, TaskApp $app, array $commandArgs, string $host, int $port, int $durationSecs)
    {
        $this->driver = $driver;
        $this->app = $app;
        $this->commandArgs = $commandArgs;
        $this->host = $host;
        $this->port = $port;
        $id = implode('_', [$driver->name, $app->name, $host, $port, implode($commandArgs)]);
        $this->id = $id;
        $this->durationSecs = $durationSecs;
    }

    public static function fromArray(array $data)
    {
        return new static(
            TaskDriver::tryFrom($data['executor']) ?? throw new InvalidArgumentException("Unknown executor: ${data['executor']}"),
            TaskApp::tryFrom($data['app']) ?? throw new InvalidArgumentException("Unknown executor: ${data['app']}"),
            $data['args'] ?? [],
            $data['host'],
            $data['port'],
            $data['durationSecs'],
        );
    }
}
