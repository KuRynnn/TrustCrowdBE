<?php

// app/Events/BugReportCreated.php
namespace App\Events;

use App\Models\BugReport;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BugReportCreated
{
    use Dispatchable, SerializesModels;

    public $bugReport;

    public function __construct(BugReport $bugReport)
    {
        $this->bugReport = $bugReport;
    }
}