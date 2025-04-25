<?php

// app/Exceptions/CrowdworkerNotFoundException.php
namespace App\Exceptions;

use Exception;

class CrowdworkerNotFoundException extends Exception
{
    public function __construct($message = "Crowdworker not found", $code = 404)
    {
        parent::__construct($message, $code);
    }
}