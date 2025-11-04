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
        // Try .php file first, then .twig file
        $phpViewPath = APP_PATH . '/Views/' . $view . '.php';
        $twigViewPath = APP_PATH . '/Views/' . $view . '.twig';

        if (file_exists($phpViewPath)) {
            // Render PHP view
            ob_start();
            extract($data);
            include $phpViewPath;
            $content = ob_get_clean();
        } elseif (file_exists($twigViewPath)) {
            // Render Twig view (placeholder for now)
            // In production, this would use Twig renderer
            $content = "<!-- Twig View: {$view} -->\n<!-- Data: " . json_encode($data) . " -->";
        } else {
            return self::notFound('View not found: ' . $view . ' (.php or .twig)');
        }

        return new static(
            $content,
            $statusCode,
            ['Content-Type' => 'text/html; charset=utf-8']
        );
    }

    public static function notFound(string $message = 'Not Found'): self
    {
        // Try to render error view, fallback to simple message
        $errorViewPath = APP_PATH . '/Views/errors/404.php';
        if (file_exists($errorViewPath)) {
            ob_start();
            include $errorViewPath;
            $content = ob_get_clean();
            return new static($content, 404, ['Content-Type' => 'text/html; charset=utf-8']);
        }

        return new static($message, 404);
    }

    public static function notImplemented(string $message = 'Not Implemented'): self
    {
        // Try to render error view, fallback to simple message
        $errorViewPath = APP_PATH . '/Views/errors/501.php';
        if (file_exists($errorViewPath)) {
            ob_start();
            include $errorViewPath;
            $content = ob_get_clean();
            return new static($content, 501, ['Content-Type' => 'text/html; charset=utf-8']);
        }

        return new static($message, 501);
    }

    public static function serviceUnavailable(string $message = 'Service Unavailable'): self
    {
        // Try to render error view, fallback to simple message
        $errorViewPath = APP_PATH . '/Views/errors/503.php';
        if (file_exists($errorViewPath)) {
            ob_start();
            include $errorViewPath;
            $content = ob_get_clean();
            return new static($content, 503, ['Content-Type' => 'text/html; charset=utf-8']);
        }

        return new static($message, 503);
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
