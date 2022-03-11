<?php

declare(strict_types=1);

namespace App\AppRunner;

interface AppRunner
{
    public function run(): void;
    public function stop(): void;
    public function isCompleted(): bool;
}
