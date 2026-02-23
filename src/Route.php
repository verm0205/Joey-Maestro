<?php

namespace Framework;

class Route
{
    public string $method;

    public string $path;

    /** @var callable */
    public $callback;

    /**
     * @param string $method
     * @param string $path
     * @param callable $callback
     */
    public function __construct(string $method, string $path, callable $callback)
    {
        $this->method = $method;
        $this->path = $path;
        $this->callback = $callback;
    }

    public function matches(string $method, string $path): bool
    {
        if ($method !== $this->method) {
            return false;
        }

        return $path === $this->path;
    }
}
