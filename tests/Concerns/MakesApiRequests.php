<?php

declare(strict_types=1);

namespace Tests\Concerns;

trait MakesApiRequests
{
    /**
     * Build an API URL for the given path and version.
     */
    protected function apiUrl(string $path = '', string $version = 'v1'): string
    {
        $path = ltrim($path, '/');

        return "/api/{$version}/{$path}";
    }
}
