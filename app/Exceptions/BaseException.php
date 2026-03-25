<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BaseException extends Exception
{
    public function render(Request $request) 
    {
        return response()->json([
            'message' => 'An error occurred.',
            'error' => $this->getMessage()
        ], $this->getCode() != 0 ? $this->getCode() : Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
