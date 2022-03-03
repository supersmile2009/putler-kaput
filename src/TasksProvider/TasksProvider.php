<?php

declare(strict_types=1);

namespace App\TasksProvider;

use App\AppRunner\AppRunner;
use App\AppRunner\BombardierRunner;
use App\AppRunner\DNSPerfRunner;
use App\AppRunner\DRipperRunner;
use App\Struct\Task;
use App\Struct\TaskApp;
use DateTimeImmutable;
use Swoole\Coroutine\Http\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function array_map;
use function array_merge;
use function array_pop;
use function json_decode;
use function usleep;

interface TasksProvider
{
    public function getTasks(): string;
}
