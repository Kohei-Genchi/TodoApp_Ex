<?php
use App\Models\Todo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TodoCleanup extends Command
{
    protected $signature = 'todos:cleanup';
    protected $description = '昨日までの完了タスクをゴミ箱に移動し、未完了タスクをINBOXに戻す';

    public function handle()
    {
        // 昨日の日付
        $yesterday = Carbon::yesterday()->format('Y-m-d');

        try {
            // 完了したタスクをゴミ箱に移動
            $completedCount = Todo::where('location', 'TODAY')
                ->where('status', 'completed')
                ->whereDate('due_date', $yesterday)
                ->update(['status' => 'trashed']);

            $this->info("完了タスク {$completedCount} 件をゴミ箱に移動しました");

            // 未完了タスクをINBOXに戻す
            $pendingCount = Todo::where('location', 'TODAY')
                ->where('status', 'pending')
                ->whereDate('due_date', $yesterday)
                ->update([
                    'location' => 'INBOX',
                    'due_date' => null,
                    'due_time' => null
                ]);

            $this->info("未完了タスク {$pendingCount} 件をINBOXに戻しました");

            Log::info("Todoタスク整理: 完了タスク {$completedCount} 件をゴミ箱に移動、未完了タスク {$pendingCount} 件をINBOXに戻しました");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            Log::error("Todoタスク整理中にエラーが発生しました: " . $e->getMessage());
            $this->error("エラーが発生しました: " . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
