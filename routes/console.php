<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\TodoCleanup;
use App\Console\Commands\SendMorningReminders;
use App\Console\Commands\SendEveningReminders;

// Inspiring quote command
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Register the commands
// In Laravel 11, we don't need to manually register commands in a Kernel file
// They're automatically discovered from the app/Console/Commands directory

// Set up scheduler
Schedule::command('todos:cleanup')
    ->dailyAt('20:27');
    // ->timezone('Asia/Kolkata');

// メール送信のスケジュール設定
Schedule::command('reminders:morning')
    ->dailyAt('20:27'); // 8:00 IST is 2:30 UTC
    // ->timezone('Asia/Kolkata');


Schedule::command('reminders:evening')
    ->dailyAt('20:27'); // 20:00 IST is 14:30 UTC
    // ->timezone('Asia/Kolkata');

// Schedule::command('todos:cleanup')
//     ->dailyAt('00:00')
//     ->timezone('UTC');

// // メール送信のスケジュール設定
// Schedule::command('reminders:morning')
//     ->dailyAt('02:30') // 8:00 IST is 2:30 UTC
//     ->timezone('UTC');

// Schedule::command('reminders:evening')
//     ->dailyAt('14:50') // 20:00 IST is 14:30 UTC
//     ->timezone('UTC');
