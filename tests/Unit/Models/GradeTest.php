<?php

namespace Tests\Unit\Models;

use App\Models\Grade;
use PHPUnit\Framework\TestCase;

class GradeTest extends TestCase
{
    public function testDefaultStatusIsZero(): void
    {
        $grade = new Grade();
        $this->assertSame(0, $grade->status);
    }

    public function testDefaultCijferIsNull(): void
    {
        $grade = new Grade();
        $this->assertNull($grade->cijfer);
    }

    public function testCanSetProperties(): void
    {
        $grade           = new Grade();
        $grade->quarter  = 'Quarter 1';
        $grade->course   = 'Programming Basics';
        $grade->ec       = 5.0;
        $grade->toetsing = 'Case Study';
        $grade->cijfer   = 8.5;
        $grade->status   = 1;

        $this->assertSame('Quarter 1', $grade->quarter);
        $this->assertSame('Programming Basics', $grade->course);
        $this->assertSame(5.0, $grade->ec);
        $this->assertSame('Case Study', $grade->toetsing);
        $this->assertSame(8.5, $grade->cijfer);
        $this->assertSame(1, $grade->status);
    }
}
