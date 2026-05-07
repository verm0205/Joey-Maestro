<?php

namespace App\Repositories;

use Framework\Database;
use App\Models\Task;
use Framework\ResponseFactory;

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
        $stmt = $this->database->run("SELECT * FROM tasks ORDER BY title")->fetchAll();
        $tasks = [];
        foreach ($stmt as $row) {
            $task = $this->fromDbRow($row);
            $tasks[] = $task;
        }
        return $tasks;
    }

    public function find(int $id): ?Task
    {
        $stmt = $this->database->run("SELECT * FROM tasks WHERE id = :id", ["id" => $id])->fetch();
        if (!$stmt) {
            return null;
        }
        $task = $this->fromDbRow($stmt);
        return $task;
    }


    public function insert(Task $task): Task|null
    {
        $stmt = $this->database->run(
            "INSERT INTO tasks (title, description, priority, status, progress, created_at, completed_at) 
                 VALUES (:title, :description, :priority, :status, :progress, :created_at, :completed_at)",
            [
                "title" => $task->title,
                "description" => $task->description,
                "priority" => $task->priority,
                "status" => $task->status,
                "progress" => $task->progress,
                "created_at" => $task->createdAt,
                "completed_at" => $task->completedAt
            ]
        );
        if ($stmt->rowCount() === 0) {
            return null;
        }
        $task->id = $this->database->getLastID();
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

    public function edit(Task $task): bool
    {
        $this->database->run(
            "UPDATE tasks 
         SET title = :title, 
             description = :description, 
             priority = :priority, 
             status = :status, 
             progress = :progress, 
             created_at = :created_at, 
             completed_at = :completed_at 
         WHERE id = :id",
            [
                "title"        => $task->title,
                "description"  => $task->description,
                "priority"     => $task->priority,
                "status"       => $task->status,
                "progress"     => $task->progress,
                "created_at"   => $task->createdAt,
                "completed_at" => $task->completedAt,
                "id"           => $task->id
            ]
        );

        return true; // Return true unless an actual PDO exception is thrown
    }

    public function delete(Task $task): bool
    {
        $stmt = $this->database->run(
            "DELETE FROM tasks WHERE id = :id",
            ["id" => $task->id]
        );
        return $stmt->rowCount() > 0;
    }
}
