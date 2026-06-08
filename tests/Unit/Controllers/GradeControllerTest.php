<?php

namespace Tests\Unit\Controllers;

use App\Controllers\GradeController;
use App\Models\Grade;
use App\Repositories\GradeRepositoryInterface;
use App\Services\AuthService;
use Framework\Request;
use Framework\Response;
use Framework\ResponseFactory;
use Framework\Session;
use PHPUnit\Framework\TestCase;

class GradeControllerTest extends TestCase
{
    private ResponseFactory $responseFactory;
    private GradeRepositoryInterface $gradeRepository;
    private AuthService $authService;
    private Session $session;
    private GradeController $controller;

    protected function setUp(): void
    {
        $this->responseFactory = $this->createMock(ResponseFactory::class);
        $this->gradeRepository = $this->createMock(GradeRepositoryInterface::class);
        $this->authService     = $this->createMock(AuthService::class);
        $this->session         = $this->createMock(Session::class);
        $this->controller      = new GradeController(
            $this->responseFactory,
            $this->gradeRepository,
            $this->authService,
            $this->session
        );
    }

    private function makeRequest(array $post = [], array $route = []): Request
    {
        $r = new Request('GET', '/dashboard', [], $post);
        $r->routeParameters = $route;
        return $r;
    }

    private function makeGrade(int $id = 1): Grade
    {
        $g           = new Grade();
        $g->id       = $id;
        $g->quarter  = 'Q1';
        $g->course   = 'ITDP';
        $g->ec       = 3.0;
        $g->toetsing = 'Portfolio';
        $g->cijfer   = 7.5;
        $g->status   = 1;
        return $g;
    }

    public function testIndexRendersGrades(): void
    {
        $this->gradeRepository->method('all')->willReturn([$this->makeGrade()]);
        $response = new Response('<html></html>', 200);
        $this->responseFactory->method('view')->willReturn($response);

        $this->assertSame($response, $this->controller->index($this->makeRequest()));
    }

    public function testCreateRedirectsWhenNotAdmin(): void
    {
        $this->authService->method('isAdmin')->willReturn(false);
        $response = new Response('', 302, 'Location: /login');
        $this->responseFactory->method('redirect')->willReturn($response);

        $this->assertSame($response, $this->controller->create($this->makeRequest()));
    }

    public function testStoreRedirectsWhenNotAdmin(): void
    {
        $this->authService->method('isAdmin')->willReturn(false);
        $response = new Response('', 302, 'Location: /login');
        $this->responseFactory->method('redirect')->willReturn($response);

        $this->assertSame($response, $this->controller->store($this->makeRequest()));
    }

    public function testEditRedirectsWhenNotAdmin(): void
    {
        $this->authService->method('isAdmin')->willReturn(false);
        $response = new Response('', 302, 'Location: /login');
        $this->responseFactory->method('redirect')->willReturn($response);

        $this->assertSame($response, $this->controller->edit($this->makeRequest([], ['id' => '1'])));
    }

    public function testDeleteRedirectsWhenNotAdmin(): void
    {
        $this->authService->method('isAdmin')->willReturn(false);
        $response = new Response('', 302, 'Location: /login');
        $this->responseFactory->method('redirect')->willReturn($response);

        $this->assertSame($response, $this->controller->delete($this->makeRequest([], ['id' => '1'])));
    }
}
