<?php

// app/Exceptions/ClientNotFoundException.php
namespace App\Exceptions;

use Exception;

class ClientNotFoundException extends Exception
{
    public function __construct($message = "Client not found", $code = 404)
    {
        parent::__construct($message, $code);
    }
}