<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\TodoCleanup;
use App\Console\Commands\SendMorningReminders;
use App\Console\Commands\SendEveningReminders;

// Inspiring quote command
Artisan::command("inspire", function () {
    $this->comment(Inspiring::quote());
})
    ->purpose("Display an inspiring quote")
    ->hourly();

// Set up scheduler
Schedule::command("todos:cleanup")->dailyAt("9:48");

// メール送信のスケジュール設定
Schedule::command("reminders:morning")->dailyAt("9:53");

Schedule::command("reminders:evening")->dailyAt("9:53");
