<?php

namespace App;

use App\Controllers\ApiController;
use App\Controllers\BlogController;
use App\Controllers\GradeController;
use App\Controllers\HomeController;
use App\Controllers\UserController;
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
        $router->addRoute('GET', '/', fn($r) => $homeController->index($r));
        $router->addRoute('GET', '/profile', fn($r) => $homeController->profile($r));
        $router->addRoute('GET', '/faq', fn($r) => $homeController->faq($r));
        $router->addRoute('GET', '/tasks-api', fn($r) => $homeController->tasksApi($r));

        $gradeController = $container->get(GradeController::class);
        $router->addRoute('GET', '/dashboard', fn($r) => $gradeController->index($r));
        $router->addRoute('GET', '/grades/create', fn($r) => $gradeController->create($r));
        $router->addRoute('POST', '/grades', fn($r) => $gradeController->store($r));
        $router->addRoute('GET', '/grades/(?<id>[0-9]+)/edit', fn($r) => $gradeController->edit($r));
        $router->addRoute('POST', '/grades/(?<id>[0-9]+)/edit', fn($r) => $gradeController->update($r));
        $router->addRoute('GET', '/grades/(?<id>[0-9]+)/delete', fn($r) => $gradeController->deleteConfirm($r));
        $router->addRoute('POST', '/grades/(?<id>[0-9]+)/delete', fn($r) => $gradeController->delete($r));

        $blogController = $container->get(BlogController::class);
        $router->addRoute('GET', '/blog', fn($r) => $blogController->index($r));
        $router->addRoute('GET', '/blog/manage', fn($r) => $blogController->manage($r));
        $router->addRoute('GET', '/blog/create', fn($r) => $blogController->create($r));
        $router->addRoute('POST', '/blog', fn($r) => $blogController->store($r));
        $router->addRoute('GET', '/blog/(?<id>[0-9]+)/edit', fn($r) => $blogController->edit($r));
        $router->addRoute('POST', '/blog/(?<id>[0-9]+)/edit', fn($r) => $blogController->update($r));
        $router->addRoute('GET', '/blog/(?<id>[0-9]+)/delete', fn($r) => $blogController->deleteConfirm($r));
        $router->addRoute('POST', '/blog/(?<id>[0-9]+)/delete', fn($r) => $blogController->delete($r));
        $router->addRoute('GET', '/blog/(?<id>[0-9]+)', fn($r) => $blogController->show($r));

        $userController = $container->get(UserController::class);
        $router->addRoute('GET', '/register', fn($r) => $userController->registerForm($r));
        $router->addRoute('POST', '/register', fn($r) => $userController->register($r));
        $router->addRoute('GET', '/login', fn($r) => $userController->loginForm($r));
        $router->addRoute('POST', '/login', fn($r) => $userController->login($r));
        $router->addRoute('POST', '/logout', fn($r) => $userController->logout($r));

        $apiController = $container->get(ApiController::class);
        $router->addRoute('GET', '/api/grades', fn($r) => $apiController->grades($r));
        $router->addRoute('GET', '/api/grades/(?<id>[0-9]+)', fn($r) => $apiController->grade($r));
    }
}
