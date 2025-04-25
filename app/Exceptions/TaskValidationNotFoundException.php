<?php

// app/Exceptions/TaskValidationNotFoundException.php
namespace App\Exceptions;

use Exception;

class TaskValidationNotFoundException extends Exception
{
    public function __construct($message = "Task Validation not found", $code = 404)
    {
        parent::__construct($message, $code);
    }
}