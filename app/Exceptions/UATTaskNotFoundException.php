<?php

// app/Exceptions/UATTaskNotFoundException.php
namespace App\Exceptions;

use Exception;

class UATTaskNotFoundException extends Exception
{
    public function __construct($message = "UAT task not found", $code = 404)
    {
        parent::__construct($message, $code);
    }
}