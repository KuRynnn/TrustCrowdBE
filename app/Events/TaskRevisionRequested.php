<?php

namespace App\Events;

use App\Models\UATTask;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskRevisionRequested
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;
    public $qaId;

    public function __construct(UATTask $task, $qaId)
    {
        $this->task = $task;
        $this->qaId = $qaId;
    }
}