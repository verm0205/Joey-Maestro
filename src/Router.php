<?php

namespace Framework;

class Router
{
    private ResponseFactory $responseFactory;

    /** @var Route[] */
    private array $routes = [];

    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function dispatch(Request $request): Response
    {
        foreach ($this->routes as $route) {
            if ($route->matches($request->method, $request->path)) {
                $callback = $route->callback;
                $request->routeParameters = $route->routeParameters;
                $response = $callback($request);
                return $response;
            }
        }

        return $this->responseFactory->notFound();
    }

    public function addRoute(string $method, string $path, callable $callback): void
    {
        $route = new Route($method, $path, $callback);
        $this->routes[] = $route;
    }
}
