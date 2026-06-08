<?php

namespace Tests\Unit\Controllers;

use App\Controllers\UserController;
use App\Models\User;
use App\Services\AuthService;
use Framework\Request;
use Framework\Response;
use Framework\ResponseFactory;
use Framework\Session;
use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase
{
    private ResponseFactory $responseFactory;
    private AuthService $authService;
    private Session $session;
    private UserController $controller;

    protected function setUp(): void
    {
        $this->responseFactory = $this->createMock(ResponseFactory::class);
        $this->authService     = $this->createMock(AuthService::class);
        $this->session         = $this->createMock(Session::class);
        $this->controller      = new UserController($this->responseFactory, $this->authService, $this->session);
    }

    private function makeRequest(array $post = [], string $path = '/'): Request
    {
        return new Request('GET', $path, [], $post);
    }

    private function fakeResponse(int $code = 200): Response
    {
        return new Response('', $code);
    }

    public function testRegisterFormRendersView(): void
    {
        $response = $this->fakeResponse();
        $this->responseFactory->expects($this->once())->method('view')
            ->with('users/register.html.twig', $this->arrayHasKey('request'))
            ->willReturn($response);

        $this->assertSame($response, $this->controller->registerForm($this->makeRequest()));
    }

    public function testLoginFormRendersView(): void
    {
        $response = $this->fakeResponse();
        $this->responseFactory->expects($this->once())->method('view')
            ->with('users/login.html.twig', $this->arrayHasKey('request'))
            ->willReturn($response);

        $this->assertSame($response, $this->controller->loginForm($this->makeRequest()));
    }

    public function testRegisterShowsErrorsWhenFieldsEmpty(): void
    {
        $response = $this->fakeResponse();
        $this->responseFactory->expects($this->once())->method('view')
            ->with('users/register.html.twig', $this->arrayHasKey('errors'))
            ->willReturn($response);

        $result = $this->controller->register($this->makeRequest([
            'name' => '', 'username' => '', 'password' => '', 'password_confirm' => ''
        ]));
        $this->assertSame($response, $result);
    }

    public function testRegisterShowsErrorWhenUsernameTooShort(): void
    {
        $response = $this->fakeResponse();
        $this->responseFactory->expects($this->once())->method('view')
            ->with('users/register.html.twig', $this->arrayHasKey('errors'))
            ->willReturn($response);

        $result = $this->controller->register($this->makeRequest([
            'name' => 'Joey', 'username' => 'ab', 'password' => 'password123', 'password_confirm' => 'password123'
        ]));
        $this->assertSame($response, $result);
    }

    public function testRegisterShowsErrorWhenPasswordTooShort(): void
    {
        $response = $this->fakeResponse();
        $this->responseFactory->expects($this->once())->method('view')
            ->with('users/register.html.twig', $this->arrayHasKey('errors'))
            ->willReturn($response);

        $result = $this->controller->register($this->makeRequest([
            'name' => 'Joey', 'username' => 'joey123', 'password' => 'short', 'password_confirm' => 'short'
        ]));
        $this->assertSame($response, $result);
    }

    public function testRegisterShowsErrorWhenPasswordsDontMatch(): void
    {
        $response = $this->fakeResponse();
        $this->responseFactory->expects($this->once())->method('view')
            ->with('users/register.html.twig', $this->arrayHasKey('errors'))
            ->willReturn($response);

        $result = $this->controller->register($this->makeRequest([
            'name' => 'Joey', 'username' => 'joey123', 'password' => 'password123', 'password_confirm' => 'different'
        ]));
        $this->assertSame($response, $result);
    }


    public function testRegisterSuccessRedirects(): void
    {
        $user = new User();
        $user->id = 1;
        $user->name = 'Joey';
        $user->username = 'joey123';

        $this->authService->method('register')->willReturn($user);
        $this->authService->method('login')->willReturn($user);

        $response = new Response('', 302, 'Location: /');
        $this->responseFactory->expects($this->once())->method('redirect')->with('/')->willReturn($response);

        $result = $this->controller->register($this->makeRequest([
            'name' => 'Joey', 'username' => 'joey123', 'password' => 'password123', 'password_confirm' => 'password123'
        ]));
        $this->assertSame($response, $result);
    }

    public function testLoginShowsErrorWhenCredentialsWrong(): void
    {
        $this->authService->method('login')->willReturn(null);

        $response = $this->fakeResponse();
        $this->responseFactory->expects($this->once())->method('view')
            ->with('users/login.html.twig', $this->arrayHasKey('error'))
            ->willReturn($response);

        $result = $this->controller->login($this->makeRequest([
            'username' => 'wrong', 'password' => 'wrong'
        ]));
        $this->assertSame($response, $result);
    }

    public function testLoginSuccessRedirects(): void
    {
        $user = new User();
        $user->id = 1;
        $this->authService->method('login')->willReturn($user);

        $response = new Response('', 302, 'Location: /');
        $this->responseFactory->expects($this->once())->method('redirect')->with('/')->willReturn($response);

        $result = $this->controller->login($this->makeRequest([
            'username' => 'joey', 'password' => 'password123'
        ]));
        $this->assertSame($response, $result);
    }

    public function testLogoutRedirectsToLogin(): void
    {
        $this->authService->expects($this->once())->method('logout');

        $response = new Response('', 302, 'Location: /login');
        $this->responseFactory->expects($this->once())->method('redirect')->with('/login')->willReturn($response);

        $result = $this->controller->logout($this->makeRequest());
        $this->assertSame($response, $result);
    }
}
