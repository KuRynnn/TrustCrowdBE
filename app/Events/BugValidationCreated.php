<?php

// app/Events/BugValidationCreated.php
namespace App\Events;

use App\Models\BugValidation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BugValidationCreated
{
    use Dispatchable, SerializesModels;

    public $bugValidation;

    public function __construct(BugValidation $bugValidation)
    {
        $this->bugValidation = $bugValidation;
    }
}
