<?php

// app/Exceptions/TestCaseNotFoundException.php
namespace App\Exceptions;

use Exception;

class TestCaseNotFoundException extends Exception
{
    public function __construct($message = "Test case not found", $code = 404)
    {
        parent::__construct($message, $code);
    }
}