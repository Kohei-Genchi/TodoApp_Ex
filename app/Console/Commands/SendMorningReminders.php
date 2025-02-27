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
    protected $signature = 'reminders:morning';
    protected $description = 'Send morning reminders to users about their tasks';


    /**
     * The console command description.
     *
     * @var string
     */

    /**
     * Execute the console command.
     */
    // In app/Console/Commands/SendMorningReminders.php

public function handle()
{
    try {
        $users = User::all();
        $count = 0;

        foreach ($users as $user) {
            // Only send if user has an email and has tasks for today
            if ($user->email && $user->todos()
                ->where('status', 'pending')
                ->whereDate('due_date', today())
                ->count() > 0) {

                $todosCount = $user->todos()
                    ->where('status', 'pending')
                    ->whereDate('due_date', today())
                    ->count();

                $user->notify(new TaskReminder(
                    'Good morning! Here\'s a reminder about your tasks for today.',
                    $todosCount
                ));

                $count++;
                $this->info("Reminder sent to {$user->email}");
            }
        }

        $this->info("Morning reminders sent to {$count} users");
        Log::info("Morning reminders sent to {$count} users");

        return Command::SUCCESS;
    } catch (\Exception $e) {
        Log::error("Error sending morning reminders: " . $e->getMessage());
        $this->error("Error sending reminders: " . $e->getMessage());

        return Command::FAILURE;
    }
}
}
