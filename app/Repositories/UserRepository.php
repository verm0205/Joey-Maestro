<?php

namespace App\Repositories;

use App\Models\User;
use Framework\Database;

class UserRepository implements UserRepositoryInterface
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /** @return User[] */
    public function all(): array
    {
        $rows = $this->database->run('SELECT * FROM users')->fetchAll();
        return array_map(fn($row) => $this->fromRow($row), $rows);
    }

    public function findById(int $id): ?User
    {
        $row = $this->database->run(
            'SELECT * FROM users WHERE id = :id',
            ['id' => $id]
        )->fetch();

        return $row ? $this->fromRow($row) : null;
    }

    public function findByUsername(string $username): ?User
    {
        $row = $this->database->run(
            'SELECT * FROM users WHERE username = :username',
            ['username' => $username]
        )->fetch();

        return $row ? $this->fromRow($row) : null;
    }

    public function insert(User $user): ?User
    {
        $stmt = $this->database->run(
            'INSERT INTO users (name, username, password, role)
             VALUES (:name, :username, :password, :role)',
            [
                'name'     => $user->name,
                'username' => $user->username,
                'password' => $user->password,
                'role'     => $user->role,
            ]
        );

        if ($stmt->rowCount() === 0) {
            return null;
        }

        $user->id = $this->database->getLastID();
        return $user;
    }

    public function update(User $user): bool
    {
        $this->database->run(
            'UPDATE users SET name = :name, username = :username,
             password = :password, role = :role WHERE id = :id',
            [
                'name'     => $user->name,
                'username' => $user->username,
                'password' => $user->password,
                'role'     => $user->role,
                'id'       => $user->id,
            ]
        );

        return true;
    }

    private function fromRow(mixed $row): User
    {
        $user           = new User();
        $user->id       = (int) $row->id;
        $user->name     = $row->name;
        $user->username = $row->username;
        $user->password = $row->password;
        $user->role     = $row->role;
        return $user;
    }
}
