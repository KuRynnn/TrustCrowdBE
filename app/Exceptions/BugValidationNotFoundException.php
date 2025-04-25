<?php

// app/Exceptions/BugValidationNotFoundException.php
namespace App\Exceptions;

use Exception;

class BugValidationNotFoundException extends Exception
{
    public function __construct($message = "Bug validation not found", $code = 404)
    {
        parent::__construct($message, $code);
    }
}