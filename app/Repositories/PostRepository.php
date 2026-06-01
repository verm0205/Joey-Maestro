<?php

namespace App\Repositories;

use App\Models\Post;
use Framework\Database;

class PostRepository implements PostRepositoryInterface
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /** @return Post[] */
    public function all(): array
    {
        $rows = $this->database->run(
            'SELECT * FROM posts ORDER BY created_at DESC'
        )->fetchAll();

        return array_map(fn($row) => $this->fromRow($row), $rows);
    }

    /** @return Post[] */
    public function allPublished(): array
    {
        $rows = $this->database->run(
            "SELECT * FROM posts WHERE status = 'published' ORDER BY created_at DESC"
        )->fetchAll();

        return array_map(fn($row) => $this->fromRow($row), $rows);
    }

    public function find(int $id): ?Post
    {
        $row = $this->database->run(
            'SELECT * FROM posts WHERE id = :id',
            ['id' => $id]
        )->fetch();

        return $row ? $this->fromRow($row) : null;
    }

    public function findByPath(string $path): ?Post
    {
        $row = $this->database->run(
            'SELECT * FROM posts WHERE path = :path',
            ['path' => $path]
        )->fetch();

        return $row ? $this->fromRow($row) : null;
    }

    public function insert(Post $post): ?Post
    {
        $stmt = $this->database->run(
            'INSERT INTO posts (title, path, body, status, created_at, updated_at)
             VALUES (:title, :path, :body, :status, NOW(), NOW())',
            [
                'title'  => $post->title,
                'path'   => $post->path,
                'body'   => $post->body,
                'status' => $post->status,
            ]
        );

        if ($stmt->rowCount() === 0) {
            return null;
        }

        $post->id = $this->database->getLastID();
        return $post;
    }

    public function update(Post $post): bool
    {
        $this->database->run(
            'UPDATE posts
             SET title = :title, path = :path, body = :body,
                 status = :status, updated_at = NOW()
             WHERE id = :id',
            [
                'title'  => $post->title,
                'path'   => $post->path,
                'body'   => $post->body,
                'status' => $post->status,
                'id'     => $post->id,
            ]
        );

        return true;
    }

    public function delete(Post $post): bool
    {
        $stmt = $this->database->run(
            'DELETE FROM posts WHERE id = :id',
            ['id' => $post->id]
        );

        return $stmt->rowCount() > 0;
    }

    public function pathExists(string $path, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $row = $this->database->run(
                'SELECT id FROM posts WHERE path = :path AND id != :id',
                ['path' => $path, 'id' => $excludeId]
            )->fetch();
        } else {
            $row = $this->database->run(
                'SELECT id FROM posts WHERE path = :path',
                ['path' => $path]
            )->fetch();
        }

        return $row !== false;
    }

    private function fromRow(mixed $row): Post
    {
        $post             = new Post();
        $post->id         = (int) $row->id;
        $post->title      = $row->title;
        $post->path       = $row->path;
        $post->body       = $row->body;
        $post->status     = $row->status;
        $post->created_at = $row->created_at;
        $post->updated_at = $row->updated_at;
        return $post;
    }
}