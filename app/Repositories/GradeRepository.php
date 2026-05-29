<?php

namespace App\Repositories;

use App\Models\Grade;
use Framework\Database;

class GradeRepository implements GradeRepositoryInterface
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /** @return Grade[] */
    public function all(): array
    {
        $rows = $this->database->run(
            'SELECT * FROM grades ORDER BY quarter, course, toetsing'
        )->fetchAll();

        return array_map(fn($row) => $this->fromRow($row), $rows);
    }

    public function find(int $id): ?Grade
    {
        $row = $this->database->run(
            'SELECT * FROM grades WHERE id = :id',
            ['id' => $id]
        )->fetch();

        return $row ? $this->fromRow($row) : null;
    }

    public function insert(Grade $grade): ?Grade
    {
        $stmt = $this->database->run(
            'INSERT INTO grades (quarter, course, ec, toetsing, cijfer, status)
             VALUES (:quarter, :course, :ec, :toetsing, :cijfer, :status)',
            [
                'quarter'  => $grade->quarter,
                'course'   => $grade->course,
                'ec'       => $grade->ec,
                'toetsing' => $grade->toetsing,
                'cijfer'   => $grade->cijfer,
                'status'   => $grade->status,
            ]
        );

        if ($stmt->rowCount() === 0) {
            return null;
        }

        $grade->id = $this->database->getLastID();
        return $grade;
    }

    public function update(Grade $grade): bool
    {
        $this->database->run(
            'UPDATE grades
             SET quarter = :quarter, course = :course, ec = :ec,
                 toetsing = :toetsing, cijfer = :cijfer, status = :status
             WHERE id = :id',
            [
                'quarter'  => $grade->quarter,
                'course'   => $grade->course,
                'ec'       => $grade->ec,
                'toetsing' => $grade->toetsing,
                'cijfer'   => $grade->cijfer,
                'status'   => $grade->status,
                'id'       => $grade->id,
            ]
        );

        return true;
    }

    public function delete(Grade $grade): bool
    {
        $stmt = $this->database->run(
            'DELETE FROM grades WHERE id = :id',
            ['id' => $grade->id]
        );

        return $stmt->rowCount() > 0;
    }

    private function fromRow(mixed $row): Grade
    {
        $grade           = new Grade();
        $grade->id       = (int) $row->id;
        $grade->quarter  = $row->quarter;
        $grade->course   = $row->course;
        $grade->ec       = (float) $row->ec;
        $grade->toetsing = $row->toetsing;
        $grade->cijfer   = $row->cijfer !== null ? (float) $row->cijfer : null;
        $grade->status   = (int) $row->status;
        return $grade;
    }
}
