<?php

namespace Tests\Unit\Models;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testDefaultRoleIsUser(): void
    {
        $user = new User();
        $this->assertSame('user', $user->role);
    }

    public function testCanSetAdminRole(): void
    {
        $user       = new User();
        $user->role = 'admin';
        $this->assertSame('admin', $user->role);
    }

    public function testCanSetProperties(): void
    {
        $user           = new User();
        $user->name     = 'Joey Vermeulen';
        $user->username = 'joey';
        $user->password = 'hashed_pass';
        $user->role     = 'admin';

        $this->assertSame('Joey Vermeulen', $user->name);
        $this->assertSame('joey', $user->username);
        $this->assertSame('hashed_pass', $user->password);
        $this->assertSame('admin', $user->role);
    }
}
