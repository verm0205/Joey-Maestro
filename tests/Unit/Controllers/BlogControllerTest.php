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

    private function makeRequest(array $post = [], array $route = []): Request
    {
        $r = new Request('GET', '/blog', [], $post);
        $r->routeParameters = $route;
        return $r;
    }

    private function makePost(int $id = 1, string $status = 'published'): Post
    {
        $p           = new Post();
        $p->id       = $id;
        $p->title    = 'Test Post';
        $p->body     = 'Test content';
        $p->status   = $status;
        $p->created_at = '2026-01-01 00:00:00';
        $p->updated_at = '2026-01-01 00:00:00';
        return $p;
    }

    private function fakeView(): Response
    {
        return new Response('<html></html>', 200);
    }

    private function fakeRedirect(string $url): Response
    {
        return new Response('', 302, 'Location: ' . $url);
    }

    // --- index ---

    public function testIndexRendersPublishedPosts(): void
    {
        $this->postRepository->method('allPublished')->willReturn([$this->makePost()]);
        $this->authService->method('isAdmin')->willReturn(false);

        $response = $this->fakeView();
        $this->responseFactory->expects($this->once())->method('view')
            ->with('blogs/index.html.twig', $this->arrayHasKey('posts'))
            ->willReturn($response);

        $this->assertSame($response, $this->controller->index($this->makeRequest()));
    }

    // --- show ---

    public function testShowReturns404WhenPostNotFound(): void
    {
        $this->postRepository->method('find')->willReturn(null);

        $response = new Response('', 404);
        $this->responseFactory->expects($this->once())->method('notFound')->willReturn($response);

        $this->assertSame($response, $this->controller->show($this->makeRequest([], ['id' => '99'])));
    }

    public function testShowReturns404WhenPostNotPublished(): void
    {
        $this->postRepository->method('find')->willReturn($this->makePost(1, 'draft'));

        $response = new Response('', 404);
        $this->responseFactory->expects($this->once())->method('notFound')->willReturn($response);

        $this->assertSame($response, $this->controller->show($this->makeRequest([], ['id' => '1'])));
    }

    public function testShowRendersPublishedPost(): void
    {
        $this->postRepository->method('find')->willReturn($this->makePost());
        $this->authService->method('isAdmin')->willReturn(false);

        $response = $this->fakeView();
        $this->responseFactory->expects($this->once())->method('view')
            ->with('blogs/show.html.twig', $this->arrayHasKey('post'))
            ->willReturn($response);

        $this->assertSame($response, $this->controller->show($this->makeRequest([], ['id' => '1'])));
    }

    // --- manage ---

    public function testManageRedirectsWhenNotAdmin(): void
    {
        $this->authService->method('isAdmin')->willReturn(false);

        $response = $this->fakeRedirect('/login');
        $this->responseFactory->expects($this->once())->method('redirect')->with('/login')->willReturn($response);

        $this->assertSame($response, $this->controller->manage($this->makeRequest()));
    }

    public function testManageRendersAllPostsWhenAdmin(): void
    {
        $this->authService->method('isAdmin')->willReturn(true);
        $this->postRepository->method('all')->willReturn([$this->makePost(), $this->makePost(2, 'draft')]);

        $response = $this->fakeView();
        $this->responseFactory->expects($this->once())->method('view')
            ->with('blogs/manage.html.twig', $this->arrayHasKey('posts'))
            ->willReturn($response);

        $this->assertSame($response, $this->controller->manage($this->makeRequest()));
    }

    // --- create ---

    public function testCreateRedirectsWhenNotAdmin(): void
    {
        $this->authService->method('isAdmin')->willReturn(false);

        $response = $this->fakeRedirect('/login');
        $this->responseFactory->expects($this->once())->method('redirect')->with('/login')->willReturn($response);

        $this->assertSame($response, $this->controller->create($this->makeRequest()));
    }

    public function testCreateRendersFormWhenAdmin(): void
    {
        $this->authService->method('isAdmin')->willReturn(true);

        $response = $this->fakeView();
        $this->responseFactory->expects($this->once())->method('view')
            ->with('blogs/create.html.twig', $this->arrayHasKey('request'))
            ->willReturn($response);

        $this->assertSame($response, $this->controller->create($this->makeRequest()));
    }

    // --- store ---

    public function testStoreRedirectsWhenNotAdmin(): void
    {
        $this->authService->method('isAdmin')->willReturn(false);

        $response = $this->fakeRedirect('/login');
        $this->responseFactory->expects($this->once())->method('redirect')->with('/login')->willReturn($response);

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

    // --- edit ---

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

    // --- update ---

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

    // --- deleteConfirm ---

    public function testDeleteConfirmRedirectsWhenNotAdmin(): void
    {
        $this->authService->method('isAdmin')->willReturn(false);

        $response = $this->fakeRedirect('/login');
        $this->responseFactory->expects($this->once())->method('redirect')->with('/login')->willReturn($response);

        $this->assertSame($response, $this->controller->deleteConfirm($this->makeRequest([], ['id' => '1'])));
    }

    public function testDeleteConfirmReturns404WhenNotFound(): void
    {
        $this->authService->method('isAdmin')->willReturn(true);
        $this->postRepository->method('find')->willReturn(null);

        $response = new Response('', 404);
        $this->responseFactory->expects($this->once())->method('notFound')->willReturn($response);

        $this->assertSame($response, $this->controller->deleteConfirm($this->makeRequest([], ['id' => '99'])));
    }

    public function testDeleteConfirmRendersView(): void
    {
        $this->authService->method('isAdmin')->willReturn(true);
        $this->postRepository->method('find')->willReturn($this->makePost());

        $response = $this->fakeView();
        $this->responseFactory->expects($this->once())->method('view')
            ->with('blogs/delete.html.twig', $this->arrayHasKey('post'))
            ->willReturn($response);

        $this->assertSame($response, $this->controller->deleteConfirm($this->makeRequest([], ['id' => '1'])));
    }

    // --- delete ---

    public function testDeleteRedirectsWhenNotAdmin(): void
    {
        $this->authService->method('isAdmin')->willReturn(false);

        $response = $this->fakeRedirect('/login');
        $this->responseFactory->expects($this->once())->method('redirect')->with('/login')->willReturn($response);

        $this->assertSame($response, $this->controller->delete($this->makeRequest([], ['id' => '1'])));
    }

    public function testDeleteReturns404WhenNotFound(): void
    {
        $this->authService->method('isAdmin')->willReturn(true);
        $this->postRepository->method('find')->willReturn(null);

        $response = new Response('', 404);
        $this->responseFactory->expects($this->once())->method('notFound')->willReturn($response);

        $this->assertSame($response, $this->controller->delete($this->makeRequest([], ['id' => '99'])));
    }

    public function testDeleteRemovesPostAndRedirects(): void
    {
        $this->authService->method('isAdmin')->willReturn(true);
        $this->postRepository->method('find')->willReturn($this->makePost());
        $this->postRepository->expects($this->once())->method('delete');

        $response = $this->fakeRedirect('/blog/manage');
        $this->responseFactory->expects($this->once())->method('redirect')->with('/blog/manage')->willReturn($response);

        $this->assertSame($response, $this->controller->delete($this->makeRequest([], ['id' => '1'])));
    }
}
