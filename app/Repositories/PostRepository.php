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

    public function insert(Post $post): ?Post
    {
        if ($post->path === '') {
            $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', $post->title) ?? '', '-'));
            $post->path = $slug ?: 'post';
        }

        $stmt = $this->database->run(
            'INSERT INTO posts (title, path, body, status)
             VALUES (:title, :path, :body, :status)',
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
             SET title = :title, body = :body, status = :status
             WHERE id = :id',
            [
                'title'  => $post->title,
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

    private function fromRow(mixed $row): Post
    {
        $post             = new Post();
        $post->id         = (int) $row->id;
        $post->title      = $row->title;
        $post->path       = $row->path ?? '';
        $post->body       = $row->body;
        $post->status     = $row->status;
        $post->created_at = $row->created_at;
        $post->updated_at = $row->updated_at;
        return $post;
    }
}
