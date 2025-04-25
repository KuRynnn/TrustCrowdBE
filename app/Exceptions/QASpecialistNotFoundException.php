<?php

// app/Exceptions/QASpecialistNotFoundException.php
namespace App\Exceptions;

use Exception;

class QASpecialistNotFoundException extends Exception
{
    public function __construct($message = "QA Specialist not found", $code = 404)
    {
        parent::__construct($message, $code);
    }
}