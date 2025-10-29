<?php

declare(strict_types=1);

namespace App\Http;

class Request
{
    protected array $query;
    protected array $request;
    protected array $server;

    public function __construct(array $query, array $request, array $server)
    {
        $this->query = $query;
        $this->request = $request;
        $this->server = $server;
    }

    public static function createFromGlobals(): self
    {
        return new static($_GET, $_POST, $_SERVER);
    }

    public function getMethod(): string
    {
        return $this->server['REQUEST_METHOD'] ?? 'GET';
    }

    public function getPath(): string
    {
        $path = $this->server['REQUEST_URI'] ?? '/';
        return parse_url($path, PHP_URL_PATH);
    }

    public function query(string $key, $default = null)
    {
        return $this->query[$key] ?? $default;
    }

    public function input(string $key, $default = null)
    {
        return $this->request[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->request);
    }
}
