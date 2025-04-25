<?php

// app/Events/UATTaskCreated.php
namespace App\Events;

use App\Models\UATTask;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UATTaskCreated
{
    use Dispatchable, SerializesModels;

    public $uatTask;

    public function __construct(UATTask $uatTask)
    {
        $this->uatTask = $uatTask;
    }
}
