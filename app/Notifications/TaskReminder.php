<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

//https://readouble.com/laravel/11.x/ja/notifications.html
// https://qiita.com/ran/items/0dede7d3f28601b2ad96
class TaskReminder extends Notification
{
    use Queueable;

    protected $message;
    protected $todosCount;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $message,int $todosCount)
    {
        $this->message=$message;
        $this->todosCount=$todosCount;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    // In app/Notifications/TaskReminder.php

public function toMail(object $notifiable): MailMessage
{
    $mailMessage = new MailMessage;
    $mailMessage->subject('Todo Task Reminder')
                ->greeting("Hello {$notifiable->name}!")
                ->line($this->message)
                ->line("You have {$this->todosCount} pending tasks for today.");

    // Add list of tasks if there are any
    if ($this->todosCount > 0) {
        $pendingTasks = $notifiable->todos()
            ->where('status', 'pending')
            ->whereDate('due_date', today())
            ->get();

        $mailMessage->line('Your pending tasks:');

        foreach ($pendingTasks as $task) {
            $mailMessage->line("- {$task->title}");
        }
    }

    return $mailMessage->action('View Tasks', url('/todos?view=today'))
                      ->line('Thank you for using our application!');
}

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
