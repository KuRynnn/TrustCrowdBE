<?php

namespace App\Events;

use App\Models\BugReport;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BugReportRevised
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $bugReport;
    public $originalBugReport;

    public function __construct(BugReport $bugReport, BugReport $originalBugReport)
    {
        $this->bugReport = $bugReport;
        $this->originalBugReport = $originalBugReport;
    }
}