<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\TaskReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendEveningReminders extends Command
{
    protected $signature = 'reminders:evening';
    protected $description = 'Send evening reminders to users about their tasks';

    public function handle()
    {
        try {
            $users = User::all();
            $count = 0;

            foreach ($users as $user) {
                $completedCount = $user->todos()
                    ->where('status', 'completed')
                    ->whereDate('due_date', today())
                    ->count();

                $pendingCount = $user->todos()
                    ->where('status', 'pending')
                    ->whereDate('due_date', today())
                    ->count();

                // Only send if user has an email and has relevant tasks for today
                if ($user->email && ($pendingCount > 0 || $completedCount > 0)) {
                    $message = "Good evening! ";
                    if ($pendingCount > 0) {
                        $message .= "You still have {$pendingCount} tasks pending for today. ";
                    }
                    if ($completedCount > 0) {
                        $message .= "Great job completing {$completedCount} tasks today! ";
                    }

                    $user->notify(new TaskReminder($message, $pendingCount));

                    // Log details about the sent notification
                    $this->info("Evening reminder sent to {$user->email} ({$user->name})");
                    Log::info("Evening reminder sent to {$user->email} ({$user->name}) - Pending: {$pendingCount}, Completed: {$completedCount}");

                    $count++;
                } else {
                    // Log why notification wasn't sent
                    if (!$user->email) {
                        $this->warning("User {$user->name} (ID: {$user->id}) has no email address, skipping notification");
                        Log::warning("Evening reminder not sent - User {$user->name} (ID: {$user->id}) has no email address");
                    } elseif ($pendingCount == 0 && $completedCount == 0) {
                        $this->info("User {$user->name} has no tasks for today, skipping notification");
                    }
                }
            }

            $this->info("Evening reminders sent to {$count} users");
            Log::info("Evening reminders sent to {$count} users");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            Log::error("Error sending evening reminders: " . $e->getMessage());
            $this->error("Error sending reminders: " . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
