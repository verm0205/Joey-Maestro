<?php

namespace App\Repositories;

use App\Models\Grade;

interface GradeRepositoryInterface
{
    /** @return Grade[] */
    public function all(): array;
    public function find(int $id): ?Grade;
    public function insert(Grade $grade): ?Grade;
    public function update(Grade $grade): bool;
    public function delete(Grade $grade): bool;
}
