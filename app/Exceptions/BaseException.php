<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

class BaseException extends Exception
{
    public function __construct(string $message, private int $httpCode)
    {
        parent::__construct($message);
    }
    public function render(Request $request) 
    {
        return response()->json([
            'message' => 'An error occurred.',
            'error' => $this->getMessage()
        ], $this->httpCode);
    }
}
