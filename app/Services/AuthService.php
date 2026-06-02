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

    public function register(User $user, string $password): ?User
    {
        // A07: Hash password with bcrypt before storing
        $user->password = password_hash($password, PASSWORD_BCRYPT);
        return $this->userRepository->insert($user);
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

        // A07: Regenerate session ID on login to prevent session fixation
        $session->regenerate();

        $session->set('user_id', (string) $user->id);
        $session->set('user_role', $user->role);

        return $user;
    }

    public function logout(Session $session): void
    {
        // A07: Regenerate session ID on logout too
        $session->regenerate();
        $session->destroy();
    }

    /**
     * Re-fetches the user from the DB on every request.
     * Constant session check — if user deleted or role changed, access is revoked immediately (A01).
     */
    public function getLoggedInUser(Session $session): ?User
    {
        $userId = $session->get('user_id');

        if ($userId === null) {
            return null;
        }

        // A01: Always verify against DB, never trust session role value alone
        $user = $this->userRepository->findById((int) $userId);

        if ($user === null) {
            $session->destroy();
            return null;
        }

        // Keep session role in sync with DB
        $session->set('user_role', $user->role);

        return $user;
    }

    public function isAdmin(Session $session): bool
    {
        $user = $this->getLoggedInUser($session);
        return $user !== null && $user->role === 'admin';
    }

    public function isLoggedIn(Session $session): bool
    {
        return $this->getLoggedInUser($session) !== null;
    }
}