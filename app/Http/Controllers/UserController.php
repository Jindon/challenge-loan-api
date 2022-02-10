<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserController
{
    public function __invoke(Request $request)
    {
        return new JsonResource($request->user());
    }
}
