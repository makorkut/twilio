<?php

declare(strict_types=1);

namespace App\Core;

use App\Http\Request;
use App\Http\Response;

/**
 * Application Core
 *
 * Main application bootstrap and request handling
 */
class Application
{
    protected Container $container;
    protected Router $router;
    protected Database $database;

    public function __construct()
    {
        $this->container = new Container();
        $this->registerCoreServices();
        $this->database = $this->container->get(Database::class);
        $this->router = $this->container->get(Router::class);
    }

    /**
     * Register core services
     */
    protected function registerCoreServices(): void
    {
        // Database
        $this->container->singleton(Database::class, function() {
            return new Database([
                'host' => env('DB_HOST'),
                'port' => env('DB_PORT', 3306),
                'database' => env('DB_DATABASE'),
                'username' => env('DB_USERNAME'),
                'password' => env('DB_PASSWORD'),
                'charset' => env('DB_CHARSET', 'utf8mb4'),
                'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            ]);
        });

        // Router
        $this->container->singleton(Router::class, function() {
            return new Router();
        });

        // Request
        $this->container->singleton(Request::class, function() {
            return Request::createFromGlobals();
        });
    }

    /**
     * Run application
     */
    public function run(): void
    {
        $request = $this->container->get(Request::class);

        // Load routes
        $this->loadRoutes();

        // Dispatch request
        $response = $this->router->dispatch($request);

        // Send response
        $response->send();
    }

    /**
     * Load route files
     */
    protected function loadRoutes(): void
    {
        // Web routes
        require BASE_PATH . '/routes/web.php';

        // API routes
        require BASE_PATH . '/routes/api.php';

        // Webhook routes
        require BASE_PATH . '/routes/webhooks.php';
    }

    /**
     * Get container instance
     */
    public function getContainer(): Container
    {
        return $this->container;
    }
}
