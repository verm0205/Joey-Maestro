<?php

namespace Tests\Unit\Controllers;

use App\Controllers\BlogController;
use App\Models\Post;
use App\Repositories\PostRepositoryInterface;
use App\Services\AuthService;
use Framework\Request;
use Framework\Response;
use Framework\ResponseFactory;
use Framework\Session;
use PHPUnit\Framework\TestCase;

class BlogControllerTest extends TestCase
{
    private ResponseFactory $responseFactory;
    private PostRepositoryInterface $postRepository;
    private AuthService $authService;
    private Session $session;
    private BlogController $controller;

    protected function setUp(): void
    {
        $this->responseFactory = $this->createMock(ResponseFactory::class);
        $this->postRepository  = $this->createMock(PostRepositoryInterface::class);
        $this->authService     = $this->createMock(AuthService::class);
        $this->session         = $this->createMock(Session::class);
        $this->controller      = new BlogController(
            $this->responseFactory,
            $this->postRepository,
            $this->authService,
            $this->session
        );
    }

    private function fakeView(): Response
    {
        return new Response('<html></html>', 200);
    }

    private function fakeRedirect(string $url): Response
    {
        return new Response('', 302, 'Location: ' . $url);
    }

    private function makeRequest(array $post = [], array $route = []): Request
    {
        $r = new Request('GET', '/blog', [], $post);
        $r->routeParameters = $route;
        return $r;
    }

    private function makePost(int $id = 1, string $status = 'published'): Post
    {
        $p             = new Post();
        $p->id         = $id;
        $p->title      = 'Test Post';
        $p->body       = 'Test content';
        $p->status     = $status;
        $p->created_at = '2026-01-01 00:00:00';
        $p->updated_at = '2026-01-01 00:00:00';
        return $p;
    }

    public function testIndexRendersPublishedPosts(): void
    {
        $this->postRepository->method('allPublished')->willReturn([$this->makePost()]);
        $this->authService->method('isAdmin')->willReturn(false);
        $response = new Response('<html></html>', 200);
        $this->responseFactory->method('view')->willReturn($response);

        $this->assertSame($response, $this->controller->index($this->makeRequest()));
    }

    public function testShowReturns404WhenPostNotFound(): void
    {
        $this->postRepository->method('find')->willReturn(null);
        $response = new Response('', 404);
        $this->responseFactory->method('notFound')->willReturn($response);

        $this->assertSame($response, $this->controller->show($this->makeRequest([], ['id' => '99'])));
    }

    public function testManageRedirectsWhenNotAdmin(): void
    {
        $this->authService->method('isAdmin')->willReturn(false);
        $response = new Response('', 302, 'Location: /login');
        $this->responseFactory->method('redirect')->willReturn($response);

        $this->assertSame($response, $this->controller->manage($this->makeRequest()));
    }

    public function testStoreRedirectsWhenNotAdmin(): void
    {
        $this->authService->method('isAdmin')->willReturn(false);
        $response = new Response('', 302, 'Location: /login');
        $this->responseFactory->method('redirect')->willReturn($response);

        $this->assertSame($response, $this->controller->store($this->makeRequest()));
    }

    public function testStoreShowsErrorsWhenFieldsEmpty(): void
    {
        $this->authService->method('isAdmin')->willReturn(true);

        $response = $this->fakeView();
        $this->responseFactory->expects($this->once())->method('view')
            ->with('blogs/create.html.twig', $this->arrayHasKey('errors'))
            ->willReturn($response);

        $result = $this->controller->store($this->makeRequest([
            'title' => '', 'body' => '', 'status' => 'draft'
        ]));
        $this->assertSame($response, $result);
    }

    public function testStoreShowsErrorForInvalidStatus(): void
    {
        $this->authService->method('isAdmin')->willReturn(true);

        $response = $this->fakeView();
        $this->responseFactory->method('view')->willReturn($response);

        $result = $this->controller->store($this->makeRequest([
            'title' => 'Hello', 'body' => 'World', 'status' => 'invalid'
        ]));
        $this->assertSame($response, $result);
    }

    public function testStoreRedirectsOnSuccess(): void
    {
        $this->authService->method('isAdmin')->willReturn(true);
        $this->postRepository->method('insert')->willReturn($this->makePost());

        $response = $this->fakeRedirect('/blog/manage');
        $this->responseFactory->expects($this->once())->method('redirect')->with('/blog/manage')->willReturn($response);

        $result = $this->controller->store($this->makeRequest([
            'title' => 'Hello World', 'body' => 'Some content here', 'status' => 'published'
        ]));
        $this->assertSame($response, $result);
    }

    public function testStoreReturnsInternalErrorWhenInsertFails(): void
    {
        $this->authService->method('isAdmin')->willReturn(true);
        $this->postRepository->method('insert')->willReturn(null);

        $response = new Response('', 500);
        $this->responseFactory->expects($this->once())->method('internalError')->willReturn($response);

        $result = $this->controller->store($this->makeRequest([
            'title' => 'Hello World', 'body' => 'Some content here', 'status' => 'published'
        ]));
        $this->assertSame($response, $result);
    }

    public function testEditRedirectsWhenNotAdmin(): void
    {
        $this->authService->method('isAdmin')->willReturn(false);

        $response = $this->fakeRedirect('/login');
        $this->responseFactory->expects($this->once())->method('redirect')->with('/login')->willReturn($response);

        $this->assertSame($response, $this->controller->edit($this->makeRequest([], ['id' => '1'])));
    }

    public function testEditReturns404WhenNotFound(): void
    {
        $this->authService->method('isAdmin')->willReturn(true);
        $this->postRepository->method('find')->willReturn(null);

        $response = new Response('', 404);
        $this->responseFactory->expects($this->once())->method('notFound')->willReturn($response);

        $this->assertSame($response, $this->controller->edit($this->makeRequest([], ['id' => '99'])));
    }

    public function testEditRendersFormWithPost(): void
    {
        $this->authService->method('isAdmin')->willReturn(true);
        $this->postRepository->method('find')->willReturn($this->makePost());

        $response = $this->fakeView();
        $this->responseFactory->expects($this->once())->method('view')
            ->with('blogs/edit.html.twig', $this->arrayHasKey('post'))
            ->willReturn($response);

        $this->assertSame($response, $this->controller->edit($this->makeRequest([], ['id' => '1'])));
    }

    public function testUpdateRedirectsWhenNotAdmin(): void
    {
        $this->authService->method('isAdmin')->willReturn(false);

        $response = $this->fakeRedirect('/login');
        $this->responseFactory->expects($this->once())->method('redirect')->with('/login')->willReturn($response);

        $this->assertSame($response, $this->controller->update($this->makeRequest([], ['id' => '1'])));
    }

    public function testUpdateReturns404WhenNotFound(): void
    {
        $this->authService->method('isAdmin')->willReturn(true);
        $this->postRepository->method('find')->willReturn(null);

        $response = new Response('', 404);
        $this->responseFactory->expects($this->once())->method('notFound')->willReturn($response);

        $this->assertSame($response, $this->controller->update($this->makeRequest([], ['id' => '99'])));
    }

    public function testUpdateShowsErrorsWhenFieldsEmpty(): void
    {
        $this->authService->method('isAdmin')->willReturn(true);
        $this->postRepository->method('find')->willReturn($this->makePost());

        $response = $this->fakeView();
        $this->responseFactory->expects($this->once())->method('view')
            ->with('blogs/edit.html.twig', $this->arrayHasKey('errors'))
            ->willReturn($response);

        $result = $this->controller->update($this->makeRequest(
            ['title' => '', 'body' => '', 'status' => 'draft'],
            ['id' => '1']
        ));
        $this->assertSame($response, $result);
    }

    public function testUpdateRedirectsOnSuccess(): void
    {
        $this->authService->method('isAdmin')->willReturn(true);
        $this->postRepository->method('find')->willReturn($this->makePost());
        $this->postRepository->method('update')->willReturn(true);

        $response = $this->fakeRedirect('/blog/manage');
        $this->responseFactory->expects($this->once())->method('redirect')->with('/blog/manage')->willReturn($response);

        $result = $this->controller->update($this->makeRequest(
            ['title' => 'Updated', 'body' => 'Updated content', 'status' => 'published'],
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
}
