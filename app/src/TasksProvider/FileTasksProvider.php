<?php

declare(strict_types=1);

namespace App\TasksProvider;

use function file_get_contents;

class FileTasksProvider implements TasksProvider
{
    private string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function getTasks(): string
    {
        $data = file_get_contents($this->filePath);
        if ($data === false) {
            return '';
        }

        return $data;
    }
}
