<?php

namespace App\Controllers;

use App\Models\User;
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

    public function registerForm(Request $request): Response
    {
        return $this->responseFactory->view('users/register.html.twig', [
            'request' => $request,
        ]);
    }

    public function register(Request $request): Response
    {
        $errors = [];

        $name     = trim($request->get('name') ?? '');
        $username = trim($request->get('username') ?? '');
        $password = $request->get('password') ?? '';
        $confirm  = $request->get('password_confirm') ?? '';

        if ($name === '') {
            $errors['name'] = 'Name is required.';
        }

        if ($username === '') {
            $errors['username'] = 'Username is required.';
        } elseif (strlen($username) < 3) {
            $errors['username'] = 'Username must be at least 3 characters.';
        }

        if (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }

        if ($password !== $confirm) {
            $errors['password_confirm'] = 'Passwords do not match.';
        }

        if (!empty($errors)) {
            return $this->responseFactory->view('users/register.html.twig', [
                'request'  => $request,
                'errors'   => $errors,
                'name'     => $name,
                'username' => $username,
            ]);
        }

        $user           = new User();
        $user->name     = $name;
        $user->username = $username;
        $user->role     = 'user';

        $result = $this->authService->register($user, $password);

        if ($result === null) {
            return $this->responseFactory->view('users/register.html.twig', [
                'request'  => $request,
                'errors'   => ['username' => 'Username already taken.'],
                'name'     => $name,
                'username' => $username,
            ]);
        }

        // Auto-login after registration
        $this->authService->login($username, $password, $this->session);
        $this->session->setFlash('success', 'Account aangemaakt! Welkom, ' . $name . '!');
        return $this->responseFactory->redirect('/');
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

        $this->session->setFlash('success', 'Welkom terug, ' . $user->name . '!');
        return $this->responseFactory->redirect('/');
    }

    public function logout(Request $request): Response
    {
        $this->session->setFlash('info', 'Je bent uitgelogd.');
        $this->authService->logout($this->session);
        return $this->responseFactory->redirect('/login');
    }
}
