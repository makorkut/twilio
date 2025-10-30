<?php

/**
 * Service Container Bootstrap
 *
 * Register all services in the dependency injection container
 */

declare(strict_types=1);

use App\Core\Container;
use App\Core\Database;
use App\Core\Router;
use App\Http\Request;
use App\Http\Response;
use App\Services\ProductService;
use App\Services\MediaService;
use App\Services\WebhookService;
use App\Services\PricingService;
use App\Services\I18nService;
use App\Services\QueueService;

return function (Container $container): void {
    // Core Services
    $container->singleton(Database::class, function () {
        try {
            return new Database([
                'host' => env('DB_HOST', 'localhost'),
                'port' => (int) env('DB_PORT', '3306'),
                'database' => env('DB_DATABASE'),
                'username' => env('DB_USERNAME'),
                'password' => env('DB_PASSWORD'),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'timeout' => 5
            ]);
        } catch (\Throwable $e) {
            error_log('Failed to create Database instance: ' . $e->getMessage());
            throw $e; // Re-throw to let Application handle it
        }
    });

    $container->singleton(Router::class, function () {
        return new Router();
    });

    $container->bind(Request::class, function () {
        return Request::createFromGlobals();
    });

    // Business Services
    $container->singleton(ProductService::class, function (Container $c) {
        return new ProductService($c->get(Database::class));
    });

    $container->singleton(MediaService::class, function (Container $c) {
        return new MediaService($c->get(Database::class));
    });

    $container->singleton(WebhookService::class, function (Container $c) {
        return new WebhookService($c->get(Database::class));
    });

    $container->singleton(PricingService::class, function (Container $c) {
        return new PricingService($c->get(Database::class));
    });

    $container->singleton(I18nService::class, function (Container $c) {
        // Detect language from request
        $request = $c->get(Request::class);
        $path = $request->getPath();

        $i18n = new I18nService($c->get(Database::class));

        // Try to get language from URL path
        $langFromPath = $i18n->getLanguageFromPath($path);
        if ($langFromPath) {
            $i18n->setLanguage($langFromPath);
        }

        return $i18n;
    });

    $container->singleton(QueueService::class, function (Container $c) {
        return new QueueService(
            $c->get(Database::class),
            $c->get(ProductService::class),
            $c->get(MediaService::class)
        );
    });
};
