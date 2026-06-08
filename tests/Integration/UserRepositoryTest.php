<?php

namespace Tests\Integration;

use App\Models\User;
use App\Repositories\UserRepository;

class UserRepositoryTest extends DatabaseTestCase
{
    private UserRepository $repository;

    protected function createTables(): void
    {
        $this->db->exec('
            CREATE TABLE users (
                id       INTEGER PRIMARY KEY AUTOINCREMENT,
                name     TEXT    NOT NULL,
                username TEXT    NOT NULL UNIQUE,
                password TEXT    NOT NULL,
                role     TEXT    NOT NULL DEFAULT "user"
            )
        ');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new UserRepository($this->db);
    }

    private function makeUser(
        string $name = 'Test User',
        string $username = 'testuser',
        string $password = 'hashed_pass',
        string $role = 'user'
    ): User {
        $u           = new User();
        $u->name     = $name;
        $u->username = $username;
        $u->password = $password;
        $u->role     = $role;
        return $u;
    }

    public function testInsertAssignsId(): void
    {
        $user   = $this->makeUser();
        $result = $this->repository->insert($user);

        $this->assertNotNull($result);
        $this->assertGreaterThan(0, $result->id);
    }

    public function testFindByIdReturnsCorrectUser(): void
    {
        $inserted = $this->repository->insert($this->makeUser('Joey', 'joey', 'pass', 'admin'));
        $found    = $this->repository->findById($inserted->id);

        $this->assertNotNull($found);
        $this->assertSame('Joey', $found->name);
        $this->assertSame('joey', $found->username);
        $this->assertSame('admin', $found->role);
    }

    public function testFindByIdReturnsNullForMissingUser(): void
    {
        $this->assertNull($this->repository->findById(999));
    }

    public function testFindByUsernameReturnsCorrectUser(): void
    {
        $this->repository->insert($this->makeUser('Joey', 'joey'));
        $found = $this->repository->findByUsername('joey');

        $this->assertNotNull($found);
        $this->assertSame('joey', $found->username);
    }

    public function testFindByUsernameReturnsNullForUnknown(): void
    {
        $this->assertNull($this->repository->findByUsername('nobody'));
    }

    public function testAllReturnsAllUsers(): void
    {
        $this->repository->insert($this->makeUser('A', 'a'));
        $this->repository->insert($this->makeUser('B', 'b'));

        $this->assertCount(2, $this->repository->all());
    }

    public function testUpdateChangesUserData(): void
    {
        $user       = $this->repository->insert($this->makeUser());
        $user->name = 'Updated Name';
        $user->role = 'admin';

        $this->repository->update($user);

        $updated = $this->repository->findById($user->id);
        $this->assertSame('Updated Name', $updated->name);
        $this->assertSame('admin', $updated->role);
    }
}
