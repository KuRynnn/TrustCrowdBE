<?php

// app/Events/TestCaseCreated.php
namespace App\Events;

use App\Models\TestCase;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TestCaseCreated
{
    use Dispatchable, SerializesModels;

    public $testCase;

    public function __construct(TestCase $testCase)
    {
        $this->testCase = $testCase;
    }
}