<?php

namespace App\Console\Commands;

use App\Models\Todo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TodoCleanup extends Command
{
    protected $signature = "todos:cleanup {--days=0 : Number of days to look back}";
    protected $description = "期限切れのタスクを整理する";

    public function handle()
    {
        $daysBack = $this->option("days");
        $targetDate = Carbon::now()->subDays($daysBack)->format("Y-m-d");

        $this->info("Target date: {$targetDate}");

        // First, let's check what tasks exist
        $totalCount = Todo::whereDate("due_date", $targetDate)->count();
        $this->info("Total tasks with due date {$targetDate}: {$totalCount}");

        $todayLocationCount = Todo::where("location", "TODAY")
            ->whereDate("due_date", $targetDate)
            ->count();
        $this->info(
            "Tasks with location='TODAY' and due date {$targetDate}: {$todayLocationCount}"
        );

        // Show statuses
        $statusCounts = Todo::whereDate("due_date", $targetDate)
            ->selectRaw("status, count(*) as count")
            ->groupBy("status")
            ->get();

        $this->info("Status counts for tasks due on {$targetDate}:");
        foreach ($statusCounts as $status) {
            $this->info("- {$status->status}: {$status->count}");
        }

        try {
            // 完了したタスクを削除
            $completedCount = Todo::where("status", "completed")
                ->where("location", "TODAY") // TODAY条件を追加
                ->whereDate("due_date", $targetDate)
                ->delete();

            $this->info("完了タスク {$completedCount} 件を削除しました");

            // 未完了タスクをINBOXに戻す
            $pendingCount = Todo::where("status", "pending")
                ->where("location", "TODAY") // TODAY条件を追加
                ->whereDate("due_date", $targetDate)
                ->update([
                    "location" => "INBOX",
                    "due_date" => null,
                    "due_time" => null,
                ]);

            $this->info("未完了タスク {$pendingCount} 件をINBOXに戻しました");

            Log::info(
                "Todoタスク整理: 完了タスク {$completedCount} 件を削除、未完了タスク {$pendingCount} 件をINBOXに戻しました"
            );

            return Command::SUCCESS;
        } catch (\Exception $e) {
            Log::error(
                "Todoタスク整理中にエラーが発生しました: " . $e->getMessage()
            );
            $this->error("エラーが発生しました: " . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
