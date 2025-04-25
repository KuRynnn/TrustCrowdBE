<?php

// app/Events/QASpecialistCreated.php
namespace App\Events;

use App\Models\QASpecialist;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QASpecialistCreated
{
    use Dispatchable, SerializesModels;

    public $qaSpecialist;

    public function __construct(QASpecialist $qaSpecialist)
    {
        $this->qaSpecialist = $qaSpecialist;
    }
}
