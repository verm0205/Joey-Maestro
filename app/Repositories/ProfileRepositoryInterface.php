<?php

namespace App\Repositories;

use App\Models\Profile;

interface ProfileRepositoryInterface
{
    public function get(): ?Profile;
    public function update(Profile $profile): bool;
}
