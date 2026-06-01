<?php

namespace App\Models;

class User
{
    public int $id;
    public string $name;
    public string $username;
    public string $password;
    public string $role;

    public function __construct()
    {
        $this->role = 'user';
    }
}
