<?php

namespace App\Exceptions;

use Exception;

class ApplicationNotFoundException extends Exception
{
    public function __construct($message = "Application not found", $code = 404)
    {
        parent::__construct($message, $code);
    }
}
