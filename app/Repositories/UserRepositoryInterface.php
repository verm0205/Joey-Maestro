<?php

namespace App\Repositories;

use App\Models\User;

interface UserRepositoryInterface
{
    /** @return User[] */
    public function all(): array;

    public function findById(int $id): ?User;

    public function findByUsername(string $username): ?User;

    public function insert(User $user): ?User;

    public function update(User $user): bool;
}
