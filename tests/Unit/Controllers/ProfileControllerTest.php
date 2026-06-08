<?php

namespace Tests\Unit\Controllers;

use App\Controllers\ProfileController;
use App\Models\Profile;
use App\Repositories\ProfileRepositoryInterface;
use App\Services\AuthService;
use Framework\Request;
use Framework\Response;
use Framework\ResponseFactory;
use Framework\Session;
use PHPUnit\Framework\TestCase;

class ProfileControllerTest extends TestCase
{
    private ResponseFactory $responseFactory;
    private ProfileRepositoryInterface $profileRepository;
    private AuthService $authService;
    private Session $session;
    private ProfileController $controller;

    protected function setUp(): void
    {
        $this->responseFactory   = $this->createMock(ResponseFactory::class);
        $this->profileRepository = $this->createMock(ProfileRepositoryInterface::class);
        $this->authService       = $this->createMock(AuthService::class);
        $this->session           = $this->createMock(Session::class);
        $this->controller        = new ProfileController(
            $this->responseFactory,
            $this->profileRepository,
            $this->authService,
            $this->session
        );
    }

    private function makeRequest(array $post = []): Request
    {
        return new Request('GET', '/profile', [], $post);
    }

    private function makeProfile(): Profile
    {
        $p = new Profile();
        $p->id            = 1;
        $p->about_me      = 'I am Joey';
        $p->eager_to_learn = 'Always learning';
        $p->perseverance  = 'Never give up';
        $p->team_player   = 'I work well in teams';
        $p->languages     = 'PHP, JS';
        $p->github_url    = 'https://github.com/verm0205';
        $p->email         = 'joey@hz.nl';
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

    public function testShowRendersProfileView(): void
    {
        $this->profileRepository->method('get')->willReturn($this->makeProfile());

        $response = $this->fakeView();
        $this->responseFactory->expects($this->once())->method('view')
            ->with('profile.html.twig', $this->arrayHasKey('profile'))
            ->willReturn($response);

        $this->assertSame($response, $this->controller->show($this->makeRequest()));
    }

    public function testEditRedirectsWhenNotAdmin(): void
    {
        $this->authService->method('isAdmin')->willReturn(false);

        $response = $this->fakeRedirect('/login');
        $this->responseFactory->expects($this->once())->method('redirect')->with('/login')->willReturn($response);

        $this->assertSame($response, $this->controller->edit($this->makeRequest()));
    }

    public function testEditRendersFormWhenAdmin(): void
    {
        $this->authService->method('isAdmin')->willReturn(true);
        $this->profileRepository->method('get')->willReturn($this->makeProfile());

        $response = $this->fakeView();
        $this->responseFactory->expects($this->once())->method('view')
            ->with('profiles/edit.html.twig', $this->arrayHasKey('profile'))
            ->willReturn($response);

        $this->assertSame($response, $this->controller->edit($this->makeRequest()));
    }

    public function testUpdateRedirectsWhenNotAdmin(): void
    {
        $this->authService->method('isAdmin')->willReturn(false);

        $response = $this->fakeRedirect('/login');
        $this->responseFactory->expects($this->once())->method('redirect')->with('/login')->willReturn($response);

        $this->assertSame($response, $this->controller->update($this->makeRequest()));
    }

    public function testUpdateSavesAndRedirects(): void
    {
        $this->authService->method('isAdmin')->willReturn(true);
        $this->profileRepository->method('get')->willReturn($this->makeProfile());
        $this->profileRepository->expects($this->once())->method('update');

        $response = $this->fakeRedirect('/profile');
        $this->responseFactory->expects($this->once())->method('redirect')->with('/profile')->willReturn($response);

        $result = $this->controller->update($this->makeRequest([
            'about_me'      => 'Updated bio',
            'eager_to_learn' => 'PHP',
            'perseverance'  => 'Strong',
            'team_player'   => 'Yes',
            'languages'     => 'PHP, JS',
            'github_url'    => 'https://github.com/verm0205',
            'email'         => 'joey@hz.nl',
        ]));
        $this->assertSame($response, $result);
    }
}
