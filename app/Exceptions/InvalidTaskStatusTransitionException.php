<?php

// app/Exceptions/InvalidTaskStatusTransitionException.php
namespace App\Exceptions;

use Exception;

class InvalidTaskStatusTransitionException extends Exception
{
    public function __construct($message = "Invalid task status transition", $code = 400)
    {
        parent::__construct($message, $code);
    }
}