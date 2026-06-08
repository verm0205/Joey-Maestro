<?php

namespace Tests\Unit;

use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use App\Services\AuthService;
use Framework\Session;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuthServiceTest extends TestCase
{
    private UserRepositoryInterface&MockObject $userRepository;
    private AuthService $authService;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->authService    = new AuthService($this->userRepository);
    }

    public function testRegisterHashesPasswordAndCallsInsert(): void
    {
        $plainPassword = 'secret123';

        $this->userRepository
            ->expects($this->once())
            ->method('insert')
            ->with($this->callback(function (User $u) use ($plainPassword): bool {
                return password_verify($plainPassword, $u->password);
            }))
            ->willReturnCallback(fn(User $u) => $u);

        $user           = new User();
        $user->name     = 'Joey';
        $user->username = 'joey';

        $result = $this->authService->register($user, $plainPassword);

        $this->assertInstanceOf(User::class, $result);
    }

    public function testRegisterReturnsNullWhenInsertFails(): void
    {
        $this->userRepository->method('insert')->willReturn(null);

        $user           = new User();
        $user->name     = 'Joey';
        $user->username = 'taken';

        $result = $this->authService->register($user, 'password123');

        $this->assertNull($result);
    }

    public function testLoginReturnsNullWhenUserNotFound(): void
    {
        $this->userRepository->method('findByUsername')->willReturn(null);
        $session = $this->createMock(Session::class);

        $result = $this->authService->login('unknown', 'password', $session);

        $this->assertNull($result);
    }

    public function testLoginReturnsNullOnWrongPassword(): void
    {
        $user           = new User();
        $user->id       = 1;
        $user->username = 'joey';
        $user->password = password_hash('correct', PASSWORD_BCRYPT);
        $user->role     = 'user';

        $this->userRepository->method('findByUsername')->willReturn($user);
        $session = $this->createMock(Session::class);

        $result = $this->authService->login('joey', 'wrong', $session);

        $this->assertNull($result);
    }

    public function testLoginSuccessRegeneratesSessionAndSetsUserData(): void
    {
        $user           = new User();
        $user->id       = 42;
        $user->username = 'joey';
        $user->password = password_hash('correct', PASSWORD_BCRYPT);
        $user->role     = 'admin';

        $this->userRepository->method('findByUsername')->willReturn($user);

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('regenerate');
        $session->expects($this->exactly(2))->method('set');

        $result = $this->authService->login('joey', 'correct', $session);

        $this->assertSame($user, $result);
    }

    public function testLogoutRegeneratesAndDestroysSession(): void
    {
        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('regenerate');
        $session->expects($this->once())->method('destroy');

        $this->authService->logout($session);
    }

    public function testGetLoggedInUserReturnsNullWhenNoSession(): void
    {
        $session = $this->createMock(Session::class);
        $session->method('get')->with('user_id')->willReturn(null);

        $result = $this->authService->getLoggedInUser($session);

        $this->assertNull($result);
    }

    public function testGetLoggedInUserDestroysSessionWhenUserDeleted(): void
    {
        $session = $this->createMock(Session::class);
        $session->method('get')->with('user_id')->willReturn('5');
        $this->userRepository->method('findById')->with(5)->willReturn(null);
        $session->expects($this->once())->method('destroy');

        $result = $this->authService->getLoggedInUser($session);

        $this->assertNull($result);
    }

    public function testGetLoggedInUserReturnsSyncedUser(): void
    {
        $user       = new User();
        $user->id   = 5;
        $user->role = 'admin';

        $session = $this->createMock(Session::class);
        $session->method('get')->with('user_id')->willReturn('5');
        $this->userRepository->method('findById')->with(5)->willReturn($user);
        $session->expects($this->once())->method('set')->with('user_role', 'admin');

        $result = $this->authService->getLoggedInUser($session);

        $this->assertSame($user, $result);
    }

    public function testIsAdminReturnsFalseWhenNotLoggedIn(): void
    {
        $session = $this->createMock(Session::class);
        $session->method('get')->willReturn(null);

        $this->assertFalse($this->authService->isAdmin($session));
    }

    public function testIsAdminReturnsFalseForRegularUser(): void
    {
        $user       = new User();
        $user->id   = 1;
        $user->role = 'user';

        $session = $this->createMock(Session::class);
        $session->method('get')->willReturn('1');
        $this->userRepository->method('findById')->willReturn($user);
        $session->method('set');

        $this->assertFalse($this->authService->isAdmin($session));
    }

    public function testIsAdminReturnsTrueForAdmin(): void
    {
        $user       = new User();
        $user->id   = 1;
        $user->role = 'admin';

        $session = $this->createMock(Session::class);
        $session->method('get')->willReturn('1');
        $this->userRepository->method('findById')->willReturn($user);
        $session->method('set');

        $this->assertTrue($this->authService->isAdmin($session));
    }

    public function testIsLoggedInReturnsTrueWhenUserExists(): void
    {
        $user     = new User();
        $user->id = 1;

        $session = $this->createMock(Session::class);
        $session->method('get')->willReturn('1');
        $this->userRepository->method('findById')->willReturn($user);
        $session->method('set');

        $this->assertTrue($this->authService->isLoggedIn($session));
    }

    public function testIsLoggedInReturnsFalseWhenNoSession(): void
    {
        $session = $this->createMock(Session::class);
        $session->method('get')->willReturn(null);

        $this->assertFalse($this->authService->isLoggedIn($session));
    }
}
