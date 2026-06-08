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

    private function fakeView(): Response
    {
        return new Response('<html></html>', 200);
    }

    private function fakeRedirect(string $url): Response
    {
        return new Response('', 302, 'Location: ' . $url);
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

    public function testCreateRendersFormWhenAdmin(): void
    {
        $this->authService->method('isAdmin')->willReturn(true);
        $response = $this->fakeView();
        $this->responseFactory->expects($this->once())->method('view')
            ->with('grades/create.html.twig', $this->arrayHasKey('request'))
            ->willReturn($response);

        $this->assertSame($response, $this->controller->create($this->makeRequest()));
    }

    public function testStoreShowsErrorsWhenFieldsEmpty(): void
    {
        $this->authService->method('isAdmin')->willReturn(true);

        $response = $this->fakeView();
        $this->responseFactory->expects($this->once())->method('view')
            ->with('grades/create.html.twig', $this->arrayHasKey('errors'))
            ->willReturn($response);

        $result = $this->controller->store($this->makeRequest([
            'quarter' => '', 'course' => '', 'ec' => '', 'toetsing' => '', 'status' => '0'
        ]));
        $this->assertSame($response, $result);
    }

    public function testStoreRedirectsOnSuccess(): void
    {
        $this->authService->method('isAdmin')->willReturn(true);
        $this->gradeRepository->method('insert')->willReturn($this->makeGrade());

        $response = $this->fakeRedirect('/dashboard');
        $this->responseFactory->expects($this->once())->method('redirect')->with('/dashboard')->willReturn($response);

        $result = $this->controller->store($this->makeRequest([
            'quarter' => 'Q1', 'course' => 'PCO', 'ec' => '2.5',
            'toetsing' => 'Tentamen', 'cijfer' => '7.5', 'status' => '1'
        ]));
        $this->assertSame($response, $result);
    }

    public function testEditRedirectsWhenNotAdmin(): void
    {
        $this->authService->method('isAdmin')->willReturn(false);
        $response = new Response('', 302, 'Location: /login');
        $this->responseFactory->method('redirect')->willReturn($response);

        $this->assertSame($response, $this->controller->edit($this->makeRequest([], ['id' => '1'])));
    }
    // --- update ---

    public function testUpdateRedirectsWhenNotAdmin(): void
    {
        $this->authService->method('isAdmin')->willReturn(false);
        $response = $this->fakeRedirect('/login');
        $this->responseFactory->expects($this->once())->method('redirect')->with('/login')->willReturn($response);

        $this->assertSame($response, $this->controller->update($this->makeRequest([], ['id' => '1'])));
    }

    public function testUpdateReturns404WhenGradeNotFound(): void
    {
        $this->authService->method('isAdmin')->willReturn(true);
        $this->gradeRepository->method('find')->willReturn(null);

        $response = new Response('', 404);
        $this->responseFactory->expects($this->once())->method('notFound')->willReturn($response);

        $this->assertSame($response, $this->controller->update($this->makeRequest([], ['id' => '99'])));
    }

    public function testUpdateShowsErrorsWhenFieldsEmpty(): void
    {
        $this->authService->method('isAdmin')->willReturn(true);
        $this->gradeRepository->method('find')->willReturn($this->makeGrade());

        $response = $this->fakeView();
        $this->responseFactory->expects($this->once())->method('view')
            ->with('grades/edit.html.twig', $this->arrayHasKey('errors'))
            ->willReturn($response);

        $result = $this->controller->update($this->makeRequest(
            ['quarter' => '', 'course' => '', 'ec' => '', 'toetsing' => '', 'status' => '0'],
            ['id' => '1']
        ));
        $this->assertSame($response, $result);
    }

    public function testUpdateRedirectsOnSuccess(): void
    {
        $this->authService->method('isAdmin')->willReturn(true);
        $this->gradeRepository->method('find')->willReturn($this->makeGrade());
        $this->gradeRepository->method('update')->willReturn(true);

        $response = $this->fakeRedirect('/dashboard');
        $this->responseFactory->expects($this->once())->method('redirect')->with('/dashboard')->willReturn($response);

        $result = $this->controller->update($this->makeRequest(
            ['quarter' => 'Q1', 'course' => 'ITDP', 'ec' => '3', 'toetsing' => 'Portfolio', 'cijfer' => '7', 'status' => '1'],
            ['id' => '1']
        ));
        $this->assertSame($response, $result);
    }
    public function testDeleteRedirectsWhenNotAdmin(): void
    {
        $this->authService->method('isAdmin')->willReturn(false);
        $response = new Response('', 302, 'Location: /login');
        $this->responseFactory->method('redirect')->willReturn($response);

        $this->assertSame($response, $this->controller->delete($this->makeRequest([], ['id' => '1'])));
    }

    public function testDeleteConfirmRendersView(): void
    {
        $this->authService->method('isAdmin')->willReturn(true);
        $this->gradeRepository->method('find')->willReturn($this->makeGrade());

        $response = $this->fakeView();
        $this->responseFactory->expects($this->once())->method('view')
            ->with('grades/delete.html.twig', $this->arrayHasKey('grade'))
            ->willReturn($response);

        $this->assertSame($response, $this->controller->deleteConfirm($this->makeRequest([], ['id' => '1'])));
    }

    public function testDeleteRedirectsOnSuccess(): void
    {
        $this->authService->method('isAdmin')->willReturn(true);
        $this->gradeRepository->method('find')->willReturn($this->makeGrade());
        $this->gradeRepository->method('delete')->willReturn(true);

        $response = $this->fakeRedirect('/dashboard');
        $this->responseFactory->expects($this->once())->method('redirect')->with('/dashboard')->willReturn($response);

        $result = $this->controller->delete($this->makeRequest([], ['id' => '1']));
        $this->assertSame($response, $result);
    }
}
