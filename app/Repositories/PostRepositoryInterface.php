<?php

namespace App\Repositories;

use App\Models\Post;

interface PostRepositoryInterface
{
    /** @return Post[] */
    public function all(): array;

    /** @return Post[] */
    public function allPublished(): array;

    public function find(int $id): ?Post;

    public function insert(Post $post): ?Post;

    public function update(Post $post): bool;

    public function delete(Post $post): bool;
}
