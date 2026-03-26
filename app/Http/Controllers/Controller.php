<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function getUserId(): int
    {
        return auth()->guard('api')->user()->id;
    }
}
