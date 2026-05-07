<?php

namespace App;

use App\Controllers\HomeController;
use App\Controllers\TaskController;
use Framework\RouteProviderInterface;
use Framework\Router;
use Framework\ServiceContainer;

class RouteProvider implements RouteProviderInterface
{
    /**
     * @throws \Exception
     */
    public function register(Router $router, ServiceContainer $container): void
    {
        $homeController = $container->get(HomeController::class);
        $router->addRoute('GET', '/', [$homeController, "index"]);
        $router->addRoute('GET', '/profile', [$homeController, "profile"]);
        $router->addRoute('GET', '/dashboard', [$homeController, "dashboard"]);
        $router->addRoute('GET', '/faq', [$homeController, "faq"]);
        $router->addRoute('GET', '/blog', [$homeController, "blogIndex"]);

        // Dynamische blogpagina's
        // Deze route vangt alles op zoals /blog/swot, /blog/studiekeuze, etc.
        $router->addRoute('GET', '/blog/(?<slug>[a-zA-Z0-9_-]+)', [$homeController, "blogShow"]);

    }
}
