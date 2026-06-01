<?php

namespace Framework;

class Kernel
{
    private Router $router;

    private ServiceContainer $container;

    private ConfigManager $configManager;

    /**
     * @param string[] $config
     * @throws \Exception
     */
    public function __construct(array $config)
    {
        $this->container = new ServiceContainer();

        $this->configManager = new ConfigManager($config);

        $debugMode = $this->configManager->get('APP_ENV') != 'production';
        $viewsPath = $this->configManager->get('VIEWS_PATH');
        $responseFactory = new ResponseFactory($debugMode, $viewsPath);
        $this->container->set(ResponseFactory::class, $responseFactory);

        $dbName = $this->configManager->get('APP_DB');
        $database = new Database(__DIR__ . '/../' . $dbName);
        $this->container->set(Database::class, $database);

        $session = new Session();
        $this->container->set(Session::class, $session);

        $this->router = new Router($responseFactory);
    }

    public function registerRoutes(RouteProviderInterface $routerProvider): void
    {
        $routerProvider->register($this->router, $this->container);
    }

    public function registerServices(ServiceProviderInterface $serviceProvider): void
    {
        $serviceProvider->register($this->container);

        // After services are registered, inject the logged-in user as a Twig global
        // so every view can access {{ currentUser }}
        $session         = $this->container->get(Session::class);
        $responseFactory = $this->container->get(ResponseFactory::class);

        $userId = $session->get('user_id');
        if ($userId !== null) {
            /** @var \App\Repositories\UserRepositoryInterface $userRepo */
            $userRepo    = $this->container->get(\App\Repositories\UserRepositoryInterface::class);
            $currentUser = $userRepo->findById((int) $userId);
            $responseFactory->addGlobal('currentUser', $currentUser);
        } else {
            $responseFactory->addGlobal('currentUser', null);
        }
    }

    public function handle(Request $request): Response
    {
        return $this->router->dispatch($request);
    }

    public function getDatabase(): Database
    {
        return $this->container->get(Database::class);
    }
}
