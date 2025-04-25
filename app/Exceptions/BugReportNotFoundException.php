<?php

// app/Exceptions/BugReportNotFoundException.php
namespace App\Exceptions;

use Exception;

class BugReportNotFoundException extends Exception
{
    public function __construct($message = "Bug report not found", $code = 404)
    {
        parent::__construct($message, $code);
    }
}