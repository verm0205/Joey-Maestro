<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use Framework\Session;

class AuthService
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function login(string $username, string $password, Session $session): ?User
    {
        $user = $this->userRepository->findByUsername($username);

        if ($user === null) {
            return null;
        }

        if (!password_verify($password, $user->password)) {
            return null;
        }

        $session->set('user_id', (string) $user->id);
        $session->set('user_role', $user->role);

        return $user;
    }

    public function logout(Session $session): void
    {
        $session->destroy();
    }

    public function getLoggedInUser(Session $session): ?User
    {
        $userId = $session->get('user_id');

        if ($userId === null) {
            return null;
        }

        return $this->userRepository->findById((int) $userId);
    }

    public function isAdmin(Session $session): bool
    {
        return $session->get('user_role') === 'admin';
    }

    public function isLoggedIn(Session $session): bool
    {
        return $session->get('user_id') !== null;
    }
}
