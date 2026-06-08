<?php

namespace Framework;

use PDO;
use PDOStatement;

class Database
{
    private PDO $connection;

    public function __construct(string $name)
    {
        $host = getenv('DB_HOST');
        $port = getenv('DB_PORT') ?: '3306';
        $dbname = getenv('DB_DATABASE');
        $user = getenv('DB_USERNAME') ?: 'root';
        $pass = getenv('DB_PASSWORD') ?: null;
        if ($host) {
            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
            try {
                $this->connection = new PDO($dsn, $user, $pass);
            } catch (\PDOException $e) {
                throw new \PDOException("Failed connecting to $user@$host: " . $e->getMessage());
            }
        } else {
            $this->connection = new PDO('sqlite:' . $name);
            $this->connection->exec('PRAGMA foreign_keys = ON;');
        }

        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    }
    public function query(string $query): PDOStatement | false
    {
        return $this->connection->query($query);
    }

    /**
     * @param string $sql
     * @param mixed[]|null $params
     * @return PDOStatement
     */
    public function run(string $sql, array|null $params = null): PDOStatement
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function prepare(string $sql): PDOStatement
    {
        return $this->connection->prepare($sql);
    }

    public function exec(string $sql): false|int
    {
        return $this->connection->exec($sql);
    }

    public function getLastID(string|null $field = null): int
    {
        return (int)$this->connection->lastInsertId($field);
    }

    public function migrate(string $migrationsDirectory): void
    {
        $files = scandir($migrationsDirectory);
        if ($files === false) {
            die('Could not read database migration files');
        }
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            echo "Migrating: " . $file . "\n";
            if ($contents = file_get_contents($migrationsDirectory . $file)) {
                $this->connection->exec($contents);
            }
        }
    }
}
