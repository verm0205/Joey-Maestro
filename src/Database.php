<?php

namespace Framework;

use PDO;
use PDOStatement;

class Database
{
    private PDO $connection;

    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->connection = new PDO("sqlite:" . $this->name);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connection->exec('PRAGMA foreign_keys = ON;');
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
