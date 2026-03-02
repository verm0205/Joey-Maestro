<?php

namespace App\Repositories;

use App\Models\Task;

interface TaskRepositoryInterface
{
    /** @return Task[] */
    public function all(): array;
    public function find(int $id): ?Task;
}
