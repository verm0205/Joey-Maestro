<?php

namespace App;

use App\Controllers\ApiController;
use App\Controllers\BlogController;
use App\Controllers\GradeController;
use App\Controllers\HomeController;
use App\Repositories\GradeRepository;
use App\Repositories\GradeRepositoryInterface;
use App\Repositories\PostRepository;
use App\Repositories\PostRepositoryInterface;
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

        $postRepository = new PostRepository($database);
        $container->set(PostRepositoryInterface::class, $postRepository);

        $homeController = new HomeController($responseFactory);
        $container->set(HomeController::class, $homeController);

        $gradeController = new GradeController($responseFactory, $gradeRepository);
        $container->set(GradeController::class, $gradeController);

        $blogController = new BlogController($responseFactory, $postRepository);
        $container->set(BlogController::class, $blogController);

        $apiController = new ApiController($responseFactory, $gradeRepository);
        $container->set(ApiController::class, $apiController);
    }
}