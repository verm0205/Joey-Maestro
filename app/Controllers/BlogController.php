<?php

namespace App\Controllers;

use App\Models\Post;
use App\Repositories\PostRepositoryInterface;
use App\Services\AuthService;
use Framework\Request;
use Framework\Response;
use Framework\ResponseFactory;
use Framework\Session;

class BlogController
{
    private ResponseFactory $responseFactory;
    private PostRepositoryInterface $postRepository;
    private AuthService $authService;
    private Session $session;

    public function __construct(
        ResponseFactory $responseFactory,
        PostRepositoryInterface $postRepository,
        AuthService $authService,
        Session $session
    ) {
        $this->responseFactory = $responseFactory;
        $this->postRepository  = $postRepository;
        $this->authService     = $authService;
        $this->session         = $session;
    }

    public function index(Request $request): Response
    {
        $posts = $this->postRepository->allPublished();

        return $this->responseFactory->view('blogs/index.html.twig', [
            'request' => $request,
            'posts'   => $posts,
            'isAdmin' => $this->authService->isAdmin($this->session),
        ]);
    }

    public function show(Request $request): Response
    {
        $id   = (int) $request->get('id');
        $post = $this->postRepository->find($id);

        if ($post === null || $post->status !== 'published') {
            return $this->responseFactory->notFound();
        }

        return $this->responseFactory->view('blogs/show.html.twig', [
            'request' => $request,
            'post'    => $post,
            'isAdmin' => $this->authService->isAdmin($this->session),
        ]);
    }

    public function manage(Request $request): Response
    {
        if (!$this->authService->isAdmin($this->session)) {
            return $this->responseFactory->redirect('/login');
        }

        $posts = $this->postRepository->all();

        return $this->responseFactory->view('blogs/manage.html.twig', [
            'request' => $request,
            'posts'   => $posts,
        ]);
    }

    public function create(Request $request): Response
    {
        if (!$this->authService->isAdmin($this->session)) {
            return $this->responseFactory->redirect('/login');
        }

        return $this->responseFactory->view('blogs/create.html.twig', [
            'request' => $request,
        ]);
    }

    public function store(Request $request): Response
    {
        if (!$this->authService->isAdmin($this->session)) {
            return $this->responseFactory->redirect('/login');
        }

        $errors = [];

        $title  = trim($request->get('title') ?? '');
        $body   = trim($request->get('body') ?? '');
        $status = $request->get('status') ?? 'draft';

        if ($title === '') {
            $errors['title'] = 'Title is required.';
        } elseif (mb_strlen($title) > 255) {
            $errors['title'] = 'Title may not exceed 255 characters.';
        }

        if ($body === '') {
            $errors['body'] = 'Content is required.';
        }

        if (!in_array($status, ['draft', 'published', 'archived'], true)) {
            $errors['status'] = 'Invalid status.';
        }

        $post         = new Post();
        $post->title  = $title;
        $post->body   = $body;
        $post->status = $status;

        if (!empty($errors)) {
            return $this->responseFactory->view('blogs/create.html.twig', [
                'request' => $request,
                'errors'  => $errors,
                'post'    => $post,
            ]);
        }

        $result = $this->postRepository->insert($post);
        if ($result === null) {
            return $this->responseFactory->internalError();
        }

        $this->session->setFlash('success', 'Post "' . $title . '" is aangemaakt.');
        return $this->responseFactory->redirect('/blog/manage');
    }

    public function edit(Request $request): Response
    {
        if (!$this->authService->isAdmin($this->session)) {
            return $this->responseFactory->redirect('/login');
        }

        $id   = (int) $request->get('id');
        $post = $this->postRepository->find($id);

        if ($post === null) {
            return $this->responseFactory->notFound();
        }

        return $this->responseFactory->view('blogs/edit.html.twig', [
            'request' => $request,
            'post'    => $post,
        ]);
    }

    public function update(Request $request): Response
    {
        if (!$this->authService->isAdmin($this->session)) {
            return $this->responseFactory->redirect('/login');
        }

        $id   = (int) $request->get('id');
        $post = $this->postRepository->find($id);

        if ($post === null) {
            return $this->responseFactory->notFound();
        }

        $errors = [];

        $title  = trim($request->get('title') ?? '');
        $body   = trim($request->get('body') ?? '');
        $status = $request->get('status') ?? 'draft';

        if ($title === '') {
            $errors['title'] = 'Title is required.';
        } elseif (mb_strlen($title) > 255) {
            $errors['title'] = 'Title may not exceed 255 characters.';
        }

        if ($body === '') {
            $errors['body'] = 'Content is required.';
        }

        if (!in_array($status, ['draft', 'published', 'archived'], true)) {
            $errors['status'] = 'Invalid status.';
        }

        $post->title  = $title;
        $post->body   = $body;
        $post->status = $status;

        if (!empty($errors)) {
            return $this->responseFactory->view('blogs/edit.html.twig', [
                'request' => $request,
                'errors'  => $errors,
                'post'    => $post,
            ]);
        }

        $this->postRepository->update($post);
        $this->session->setFlash('success', 'Post "' . $post->title . '" is bijgewerkt.');
        return $this->responseFactory->redirect('/blog/manage');
    }

    public function deleteConfirm(Request $request): Response
    {
        if (!$this->authService->isAdmin($this->session)) {
            return $this->responseFactory->redirect('/login');
        }

        $id   = (int) $request->get('id');
        $post = $this->postRepository->find($id);

        if ($post === null) {
            return $this->responseFactory->notFound();
        }

        return $this->responseFactory->view('blogs/delete.html.twig', [
            'request' => $request,
            'post'    => $post,
        ]);
    }

    public function delete(Request $request): Response
    {
        if (!$this->authService->isAdmin($this->session)) {
            return $this->responseFactory->redirect('/login');
        }

        $id   = (int) $request->get('id');
        $post = $this->postRepository->find($id);

        if ($post === null) {
            return $this->responseFactory->notFound();
        }

        $this->postRepository->delete($post);
        $this->session->setFlash('success', 'Post "' . $post->title . '" is verwijderd.');
        return $this->responseFactory->redirect('/blog/manage');
    }
}
