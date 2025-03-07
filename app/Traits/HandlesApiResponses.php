<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait HandlesApiResponses
{
    /**
     * 成功レスポンスを生成
     */
    protected function successResponse($data, string $message = '', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $code);
    }

    /**
     * エラーレスポンスを生成
     */
    protected function errorResponse(string $message, int $code = 500): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $code);
    }
}
