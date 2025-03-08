<?php

namespace App\Traits;

use App\Models\Todo;
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

    /**
     * タスクの場所（location）を設定
     *
     * @param Todo $todo 対象タスク
     * @return void
     */
    protected function handleTaskLocation(Todo $todo): void
    {
        if (!$todo->due_date) {
            $todo->location = Todo::LOCATION_INBOX;
            return;
        }

        $todo->location = $todo->due_date->isToday()
            ? Todo::LOCATION_TODAY
            : Todo::LOCATION_SCHEDULED;
    }
}
