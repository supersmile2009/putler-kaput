<?php

declare(strict_types=1);

namespace App\TasksProvider;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class GuzzleTasksProvider implements TasksProvider
{
    private string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function getTasks(): string
    {
        $client = new Client();
        try {
            $res = $client->get($this->url);
        } catch (GuzzleException $e) {
            return '';
        }

        return $res->getBody()->getContents();
    }
}
