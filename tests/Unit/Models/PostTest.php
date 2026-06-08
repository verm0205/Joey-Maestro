<?php

namespace Tests\Unit\Models;

use App\Models\Post;
use PHPUnit\Framework\TestCase;

class PostTest extends TestCase
{
    public function testDefaultStatusIsDraft(): void
    {
        $post = new Post();
        $this->assertSame('draft', $post->status);
    }

    public function testDefaultTimestampsAreEmptyStrings(): void
    {
        $post = new Post();
        $this->assertSame('', $post->created_at);
        $this->assertSame('', $post->updated_at);
    }

    public function testCanSetProperties(): void
    {
        $post         = new Post();
        $post->title  = 'My First Post';
        $post->body   = 'Hello world!';
        $post->status = 'published';

        $this->assertSame('My First Post', $post->title);
        $this->assertSame('Hello world!', $post->body);
        $this->assertSame('published', $post->status);
    }
}
