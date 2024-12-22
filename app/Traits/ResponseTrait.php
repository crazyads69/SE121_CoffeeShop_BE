<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ResponseTrait
{
    public function successResponse($data = [], $message = '', $isAnalytics = false): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $data,
            'message' => $message,
            'isAnalytics' => $isAnalytics
        ]);
    }

    public function errorResponse($message = '', $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data'    => [],
            'message' => $message
        ], $status);
    }
}
