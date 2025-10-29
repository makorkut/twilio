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
        // Load service registrations from bootstrap
        $bootstrap = require APP_PATH . '/bootstrap.php';
        $bootstrap($this->container);
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
