<?php

namespace Tests\Integration;

use Framework\Database;
use PHPUnit\Framework\TestCase;

abstract class DatabaseTestCase extends TestCase
{
    protected Database $db;

    protected function setUp(): void
    {
        // SQLite in-memory — no env vars set so Database uses sqlite: path
        $this->db = new Database(':memory:');
        $this->createTables();
    }

    abstract protected function createTables(): void;
}
