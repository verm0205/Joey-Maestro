<?php

namespace Framework;

class Kernel
{
    private Router $router;

    private ServiceContainer $container;

    private ConfigManager $configManager;

    /** @var string[] */
    private array $publicRoutes = [
        '/login',
        '/register',
    ];

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
    }

    public function handle(Request $request): Response
    {
        $session         = $this->container->get(Session::class);
        $responseFactory = $this->container->get(ResponseFactory::class);

        // If the user is not logged in and the route is not public, redirect to login
        if (!in_array($request->path, $this->publicRoutes, true) && $session->get('user_id') === null) {
            return $responseFactory->redirect('/login');
        }

        return $this->router->dispatch($request);
    }

    public function getDatabase(): Database
    {
        return $this->container->get(Database::class);
    }
}
