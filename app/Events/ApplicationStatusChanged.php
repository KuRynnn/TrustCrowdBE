<?php

namespace App\Events;

use App\Models\Application;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApplicationStatusChanged
{
    use Dispatchable, SerializesModels;

    public $application;
    public $newStatus;

    public function __construct(Application $application, string $newStatus)
    {
        $this->application = $application;
        $this->newStatus = $newStatus;
    }
}
