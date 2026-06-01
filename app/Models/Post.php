<?php

namespace App\Models;

class Post
{
    public int $id;
    public string $title;
    public string $path;
    public string $body;
    public string $status;
    public string $created_at;
    public string $updated_at;

    public function __construct()
    {
        $this->status = 'draft';
        $this->created_at = '';
        $this->updated_at = '';
    }
}
