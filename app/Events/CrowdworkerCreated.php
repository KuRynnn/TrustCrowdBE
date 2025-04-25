<?php

// app/Events/CrowdworkerCreated.php
namespace App\Events;

use App\Models\Crowdworker;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CrowdworkerCreated
{
    use Dispatchable, SerializesModels;

    public $crowdworker;

    public function __construct(Crowdworker $crowdworker)
    {
        $this->crowdworker = $crowdworker;
    }
}
