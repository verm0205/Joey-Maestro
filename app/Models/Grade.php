<?php

namespace App\Models;

class Grade
{
    public int $id;
    public string $quarter;
    public string $course;
    public float $ec;
    public string $toetsing;
    public ?float $cijfer;
    public int $status;

    public function __construct()
    {
        $this->status = 0;
        $this->cijfer = null;
    }
}
