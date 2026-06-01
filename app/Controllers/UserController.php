<?php

namespace App\Controllers;

use App\Services\AuthService;
use Framework\Request;
use Framework\Response;
use Framework\ResponseFactory;
use Framework\Session;

class UserController
{
    private ResponseFactory $responseFactory;
    private AuthService $authService;
    private Session $session;

    public function __construct(ResponseFactory $responseFactory, AuthService $authService, Session $session)
    {
        $this->responseFactory = $responseFactory;
        $this->authService     = $authService;
        $this->session         = $session;
    }

    public function loginForm(Request $request): Response
    {
        return $this->responseFactory->view('users/login.html.twig', [
            'request' => $request,
        ]);
    }

    public function login(Request $request): Response
    {
        $username = trim($request->get('username') ?? '');
        $password = $request->get('password') ?? '';

        $user = $this->authService->login($username, $password, $this->session);

        if ($user === null) {
            return $this->responseFactory->view('users/login.html.twig', [
                'request'  => $request,
                'error'    => 'Invalid username or password.',
                'username' => $username,
            ]);
        }

        return $this->responseFactory->redirect('/');
    }

    public function logout(Request $request): Response
    {
        $this->authService->logout($this->session);
        return $this->responseFactory->redirect('/');
    }
}
