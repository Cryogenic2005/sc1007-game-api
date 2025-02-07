<?php

declare(strict_types=1);

namespace App;

use DI\ContainerBuilder;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Handlers\Strategies\RequestResponseArgs;
use Middlewares\TrailingSlash;

class AppBuilder
{
    private App $app;

    public function __construct()
    {
        $this->addContainer();

        // Create a new Slim app
        $this->app = AppFactory::create();
        
        // Set the default invocation strategy
        $this->app->getRouteCollector()->setDefaultInvocationStrategy(new RequestResponseArgs());

        $this->addMiddleware();

        // Add the routes
        $addRoutes = require APP_ROOT . '/config/routes.php';
        $addRoutes($this->app);
    }

    public function get(): App
    {
        return $this->app;
    }

    private function addContainer()
    {
        $containerBuilder = new ContainerBuilder();
        $container = $containerBuilder->addDefinitions(APP_ROOT . '/config/definitions.php')
                                      ->build();
        
        AppFactory::setContainer($container);
    }

    private function addMiddleware()
    {
        $this->app->addBodyParsingMiddleware(); // Add body parsing middleware
        $this->app->addErrorMiddleware(displayErrorDetails: true,
                                logErrors: true, 
                                logErrorDetails: true); // Add error handling middleware
        $this->app->add(TrailingSlash::class); // Add trailing slash middleware
    }
}