<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\TaskReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendMorningReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "reminders:morning";
    protected $description = "Send morning reminders to users about their tasks";

    /**
     * The console command description.
     *
     * @var string
     */

    /**
     * Execute the console command.
     */
    // In app/Console/Commands/SendMorningReminders.php

    // app/Console/Commands/SendMorningReminders.php の修正例

    public function handle()
    {
        try {
            $this->info("実行日時: " . now()->format("Y-m-d H:i:s"));

            $users = User::all();
            $this->info("総ユーザー数: " . $users->count());

            $count = 0;

            foreach ($users as $user) {
                $pendingTasks = $user
                    ->todos()
                    ->where("status", "pending")
                    ->whereDate("due_date", today())
                    ->get();

                $this->info(
                    "{$user->name}(ID:{$user->id}) - 今日のタスク: {$pendingTasks->count()}件"
                );

                if ($pendingTasks->count() > 0) {
                    foreach ($pendingTasks as $task) {
                        $this->info(
                            "- タスク: {$task->title}, 期限: {$task->due_date}"
                        );
                    }
                }

                if ($user->email && $pendingTasks->count() > 0) {
                    try {
                        $user->notify(
                            new TaskReminder(
                                "今日のタスクのリマインダーです",
                                $pendingTasks->count()
                            )
                        );
                        $count++;
                        $this->info("通知送信: {$user->email}");
                    } catch (\Exception $e) {
                        $this->error("通知送信エラー: " . $e->getMessage());
                    }
                } else {
                    if (!$user->email) {
                        $this->warn("メールアドレスなし: {$user->name}");
                    }
                }
            }

            $this->info("通知送信完了: {$count}ユーザー");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("エラー発生: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
