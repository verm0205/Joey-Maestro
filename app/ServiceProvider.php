<?php

namespace App;

use App\Controllers\ApiController;
use App\Controllers\GradeController;
use App\Controllers\HomeController;
use App\Controllers\TaskController;
use App\Repositories\GradeRepository;
use App\Repositories\GradeRepositoryInterface;
use App\Repositories\TaskRepository;
use App\Repositories\TaskRepositoryInterface;
use Exception;
use Framework\Database;
use Framework\ResponseFactory;
use Framework\ServiceContainer;
use Framework\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @throws Exception
     */
    public function register(ServiceContainer $container): void
    {
        $responseFactory = $container->get(ResponseFactory::class);
        $database        = $container->get(Database::class);

        $gradeRepository = new GradeRepository($database);
        $container->set(GradeRepositoryInterface::class, $gradeRepository);

        $homeController = new HomeController($responseFactory);
        $container->set(HomeController::class, $homeController);

        $gradeController = new GradeController($responseFactory, $gradeRepository);
        $container->set(GradeController::class, $gradeController);

        $apiController = new ApiController($responseFactory, $gradeRepository);
        $container->set(ApiController::class, $apiController);
    }
}
