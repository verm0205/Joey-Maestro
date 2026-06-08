<?php

namespace Tests\Unit\Controllers;

use App\Controllers\HomeController;
use Framework\Request;
use Framework\Response;
use Framework\ResponseFactory;
use PHPUnit\Framework\TestCase;

class HomeControllerTest extends TestCase
{
    private ResponseFactory $responseFactory;
    private HomeController $controller;

    protected function setUp(): void
    {
        $this->responseFactory = $this->createMock(ResponseFactory::class);
        $this->controller      = new HomeController($this->responseFactory);
    }

    private function makeRequest(string $path = '/'): Request
    {
        return new Request('GET', $path, [], []);
    }

    private function fakeResponse(): Response
    {
        return new Response('<html></html>', 200);
    }

    public function testIndexRendersView(): void
    {
        $request  = $this->makeRequest('/');
        $response = $this->fakeResponse();

        $this->responseFactory
            ->expects($this->once())
            ->method('view')
            ->with('index.html.twig', $this->arrayHasKey('request'))
            ->willReturn($response);

        $result = $this->controller->index($request);
        $this->assertSame($response, $result);
    }

    public function testProfileRendersView(): void
    {
        $request  = $this->makeRequest('/profile');
        $response = $this->fakeResponse();

        $this->responseFactory
            ->expects($this->once())
            ->method('view')
            ->with('profile.html.twig', $this->arrayHasKey('request'))
            ->willReturn($response);

        $result = $this->controller->profile($request);
        $this->assertSame($response, $result);
    }

    public function testFaqRendersView(): void
    {
        $request  = $this->makeRequest('/faq');
        $response = $this->fakeResponse();

        $this->responseFactory
            ->expects($this->once())
            ->method('view')
            ->with('faq.html.twig', $this->arrayHasKey('request'))
            ->willReturn($response);

        $result = $this->controller->faq($request);
        $this->assertSame($response, $result);
    }

    public function testTasksApiRendersView(): void
    {
        $request  = $this->makeRequest('/tasks-api');
        $response = $this->fakeResponse();

        $this->responseFactory
            ->expects($this->once())
            ->method('view')
            ->with('grades-api.html.twig', $this->arrayHasKey('request'))
            ->willReturn($response);

        $result = $this->controller->tasksApi($request);
        $this->assertSame($response, $result);
    }
}
