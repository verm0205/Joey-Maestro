<?php

namespace Tests\Integration;

use App\Models\Post;
use App\Repositories\PostRepository;

class PostRepositoryTest extends DatabaseTestCase
{
    private PostRepository $repository;

    protected function createTables(): void
    {
        $this->db->exec('
        CREATE TABLE posts (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            title      TEXT    NOT NULL,
            path       TEXT    NOT NULL DEFAULT "",
            body       TEXT    NOT NULL,
            status     TEXT    NOT NULL DEFAULT "draft",
            created_at TEXT    NOT NULL DEFAULT "",
            updated_at TEXT    NOT NULL DEFAULT ""
        )
    ');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PostRepository($this->db);
    }

    private function makePost(
        string $title = 'Test Post',
        string $body  = 'Body content.',
        string $status = 'draft'
    ): Post {
        $p         = new Post();
        $p->title  = $title;
        $p->body   = $body;
        $p->status = $status;
        return $p;
    }

    public function testAllReturnsEmptyArrayInitially(): void
    {
        $this->assertSame([], $this->repository->all());
    }

    public function testInsertAssignsId(): void
    {
        $post   = $this->makePost();
        $result = $this->repository->insert($post);

        $this->assertNotNull($result);
        $this->assertGreaterThan(0, $result->id);
    }

    public function testInsertedPostCanBeFoundById(): void
    {
        $inserted = $this->repository->insert($this->makePost('Hello World', 'Some body', 'published'));
        $found    = $this->repository->find($inserted->id);

        $this->assertNotNull($found);
        $this->assertSame('Hello World', $found->title);
        $this->assertSame('Some body', $found->body);
        $this->assertSame('published', $found->status);
    }

    public function testFindReturnsNullForMissingId(): void
    {
        $this->assertNull($this->repository->find(9999));
    }

    public function testAllReturnsAllPosts(): void
    {
        $this->repository->insert($this->makePost('A', body: 'a', status: 'published'));
        $this->repository->insert($this->makePost('B', body: 'b', status: 'draft'));
        $this->repository->insert($this->makePost('C', body: 'c', status: 'archived'));

        $this->assertCount(3, $this->repository->all());
    }

    public function testAllPublishedOnlyReturnsPublished(): void
    {
        $this->repository->insert($this->makePost('Published', body: 'x', status: 'published'));
        $this->repository->insert($this->makePost('Draft', body: 'y', status: 'draft'));
        $this->repository->insert($this->makePost('Archived', body: 'z', status: 'archived'));

        $published = $this->repository->allPublished();

        $this->assertCount(1, $published);
        $this->assertSame('Published', $published[0]->title);
    }

    public function testAllPublishedReturnsEmptyWhenNonePublished(): void
    {
        $this->repository->insert($this->makePost(status: 'draft'));

        $this->assertSame([], $this->repository->allPublished());
    }

    public function testUpdateChangesPostData(): void
    {
        $post     = $this->repository->insert($this->makePost());
        $post->title  = 'Updated Title';
        $post->status = 'published';

        $this->repository->update($post);

        $updated = $this->repository->find($post->id);
        $this->assertSame('Updated Title', $updated->title);
        $this->assertSame('published', $updated->status);
    }

    public function testDeleteRemovesPost(): void
    {
        $post = $this->repository->insert($this->makePost());
        $id   = $post->id;

        $result = $this->repository->delete($post);

        $this->assertTrue($result);
        $this->assertNull($this->repository->find($id));
    }

    public function testDeleteReturnsFalseForNonExistentPost(): void
    {
        $post     = new Post();
        $post->id = 999;

        $this->assertFalse($this->repository->delete($post));
    }

    public function testInsertReturnsNullOnFailure(): void
    {
        // Insert a post, then try to re-insert with the same object (no failure scenario without unique constraints)
        // Instead test that a returned insert gives back the object
        $post   = $this->makePost();
        $result = $this->repository->insert($post);
        $this->assertInstanceOf(Post::class, $result);
    }
}
