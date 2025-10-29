<?php

declare(strict_types=1);

namespace App\Http;

class Response
{
    protected string $content;
    protected int $statusCode;
    protected array $headers;

    public function __construct(string $content = '', int $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public static function json($data, int $statusCode = 200): self
    {
        return new static(
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $statusCode,
            ['Content-Type' => 'application/json; charset=utf-8']
        );
    }

    public static function html(string $view, array $data = [], int $statusCode = 200): self
    {
        // Simple template rendering (in production, use Twig)
        $viewPath = APP_PATH . '/Views/' . $view . '.twig';

        if (!file_exists($viewPath)) {
            return self::notFound('View not found: ' . $view);
        }

        // For now, return a placeholder
        // In production, this would use Twig renderer
        $content = "<!-- View: {$view} -->\n<!-- Data: " . json_encode($data) . " -->";

        return new static(
            $content,
            $statusCode,
            ['Content-Type' => 'text/html; charset=utf-8']
        );
    }

    public static function notFound(string $message = 'Not Found'): self
    {
        return new static($message, 404);
    }

    public static function redirect(string $url, int $statusCode = 302): self
    {
        return new static('', $statusCode, ['Location' => $url]);
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        echo $this->content;
    }
}
