<?php

namespace App\Repositories;

use Framework\Database;
use App\Models\Task;
use PDO;

class TaskRepository implements TaskRepositoryInterface
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * @return Task[]
     */
    public function all(): array
    {
        $stmt = $this->database->run("SELECT * FROM tasks ORDER BY title")->fetchAll(PDO::FETCH_OBJ);
        $tasks = [];
        foreach ($stmt as $row) {
            $task = $this->fromDbRow($row);
            $tasks[] = $task;
        }
        return $tasks;
    }

    public function find(int $id): ?Task
    {
        $stmt = $this->database->run("SELECT * FROM tasks WHERE id = :id", ["id" => $id])->fetch(PDO::FETCH_OBJ);
        if (!$stmt) {
            return null;
        }
        $task = $this->fromDbRow($stmt);
        return $task;
    }

    /**
     * @param mixed $row
     * @return Task
     */
    private function fromDbRow(mixed $row): Task
    {
        $task = new Task();
        $task->id = $row->id;
        $task->title = $row->title;
        $task->description = $row->description;
        $task->priority = $row->priority;
        $task->status = $row->status;
        $task->progress = $row->progress;
        $task->createdAt = $row->created_at;
        $task->completedAt = $row->completed_at;
        return $task;
    }
}
