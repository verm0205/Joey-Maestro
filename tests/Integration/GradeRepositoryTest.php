<?php

namespace Tests\Integration;

use App\Models\Grade;
use App\Repositories\GradeRepository;

class GradeRepositoryTest extends DatabaseTestCase
{
    private GradeRepository $repository;

    protected function createTables(): void
    {
        $this->db->exec('
            CREATE TABLE grades (
                id       INTEGER PRIMARY KEY AUTOINCREMENT,
                quarter  TEXT    NOT NULL,
                course   TEXT    NOT NULL,
                ec       REAL    NOT NULL,
                toetsing TEXT    NOT NULL,
                cijfer   REAL,
                status   INTEGER NOT NULL DEFAULT 0
            )
        ');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new GradeRepository($this->db);
    }

    private function makeGrade(
        string $quarter = 'Q1',
        string $course = 'PHP',
        float $ec = 5.0,
        string $toetsing = 'Project',
        ?float $cijfer = null,
        int $status = 0
    ): Grade {
        $g           = new Grade();
        $g->quarter  = $quarter;
        $g->course   = $course;
        $g->ec       = $ec;
        $g->toetsing = $toetsing;
        $g->cijfer   = $cijfer;
        $g->status   = $status;
        return $g;
    }

    public function testAllReturnsEmptyArrayInitially(): void
    {
        $this->assertSame([], $this->repository->all());
    }

    public function testInsertAssignsId(): void
    {
        $grade = $this->makeGrade();
        $result = $this->repository->insert($grade);

        $this->assertNotNull($result);
        $this->assertGreaterThan(0, $result->id);
    }

    public function testInsertedGradeCanBeRetrievedById(): void
    {
        $grade = $this->makeGrade('Quarter 2', 'OOP', 10.0, 'Exam', 8.0, 1);
        $inserted = $this->repository->insert($grade);

        $found = $this->repository->find($inserted->id);

        $this->assertNotNull($found);
        $this->assertSame('Quarter 2', $found->quarter);
        $this->assertSame('OOP', $found->course);
        $this->assertSame(10.0, $found->ec);
        $this->assertSame(8.0, $found->cijfer);
        $this->assertSame(1, $found->status);
    }

    public function testFindReturnsNullForMissingId(): void
    {
        $this->assertNull($this->repository->find(999));
    }

    public function testAllReturnsAllInsertedGrades(): void
    {
        $this->repository->insert($this->makeGrade('Q1', 'Math'));
        $this->repository->insert($this->makeGrade('Q1', 'PHP'));
        $this->repository->insert($this->makeGrade('Q2', 'OOP'));

        $this->assertCount(3, $this->repository->all());
    }

    public function testUpdateChangesGradeData(): void
    {
        $grade   = $this->repository->insert($this->makeGrade());
        $grade->cijfer = 9.0;
        $grade->status = 1;

        $this->repository->update($grade);

        $updated = $this->repository->find($grade->id);
        $this->assertSame(9.0, $updated->cijfer);
        $this->assertSame(1, $updated->status);
    }

    public function testDeleteRemovesGrade(): void
    {
        $grade = $this->repository->insert($this->makeGrade());
        $id    = $grade->id;

        $result = $this->repository->delete($grade);

        $this->assertTrue($result);
        $this->assertNull($this->repository->find($id));
    }

}
