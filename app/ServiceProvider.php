<?php

namespace App;

use App\Controllers\ApiController;
use App\Controllers\BlogController;
use App\Controllers\GradeController;
use App\Controllers\HomeController;
use App\Controllers\UserController;
use App\Repositories\GradeRepository;
use App\Repositories\GradeRepositoryInterface;
use App\Repositories\PostRepository;
use App\Repositories\PostRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\UserRepositoryInterface;
use App\Services\AuthService;
use Exception;
use Framework\Database;
use Framework\ResponseFactory;
use Framework\ServiceContainer;
use Framework\ServiceProviderInterface;
use Framework\Session;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(ServiceContainer $container): void
    {
        $responseFactory = $container->get(ResponseFactory::class);
        $database        = $container->get(Database::class);
        $session         = $container->get(Session::class);

        $gradeRepository = new GradeRepository($database);
        $container->set(GradeRepositoryInterface::class, $gradeRepository);

        $postRepository = new PostRepository($database);
        $container->set(PostRepositoryInterface::class, $postRepository);

        $userRepository = new UserRepository($database);
        $container->set(UserRepositoryInterface::class, $userRepository);

        $profileRepository = new \App\Repositories\ProfileRepository($database);
        $container->set(\App\Repositories\ProfileRepositoryInterface::class, $profileRepository);

        $authService = new AuthService($userRepository);
        $container->set(AuthService::class, $authService);

        $homeController = new HomeController($responseFactory);
        $container->set(HomeController::class, $homeController);

        $gradeController = new GradeController($responseFactory, $gradeRepository, $authService, $session);
        $container->set(GradeController::class, $gradeController);

        $blogController = new BlogController($responseFactory, $postRepository, $authService, $session);
        $container->set(BlogController::class, $blogController);

        $userController = new UserController($responseFactory, $authService, $session);
        $container->set(UserController::class, $userController);

        $apiController = new ApiController($responseFactory, $gradeRepository);
        $container->set(ApiController::class, $apiController);

        $profileController = new \App\Controllers\ProfileController(
            $responseFactory,
            $profileRepository,
            $authService,
            $session
        );
        $container->set(\App\Controllers\ProfileController::class, $profileController);

        $userId = $session->get('user_id');
        if ($userId !== null) {
            $currentUser = $userRepository->findById((int) $userId);
            $responseFactory->addGlobal('currentUser', $currentUser);
        } else {
            $responseFactory->addGlobal('currentUser', null);
        }

        $responseFactory->addGlobal('flashMessages', $session->getFlash());
    }
}
