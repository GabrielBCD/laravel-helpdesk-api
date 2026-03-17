<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function testLogin(Request $request): JsonResponse
    {
        return response()->json([
            'method' => $request->getMethod(),
            'path' => $request->getPathInfo(),
            'headers' => [
                'content-type' => $request->header('Content-Type'),
                'accept' => $request->header('Accept'),
            ],
            'body' => $request->all(),
        ]);
    }
}

